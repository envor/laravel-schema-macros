<?php

namespace Envor\SchemaMacros;

use Carbon\Carbon;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Stringable;

class SchemaMacrosServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-schema-macros')
            ->hasConfigFile();
    }

    public function packageBooted()
    {
        $this->bootMysqlMacros();
        $this->bootSQLiteMacros();
    }

    protected function bootMysqlMacros()
    {
        MySqlBuilder::macro('mysqlDatabaseExists', function (string|Stringable $database): bool {
            /** @var \Illuminate\Database\MySqlBuilder $this */
            return (bool) $this->getConnection()->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$database}'");
        });

        MySqlBuilder::macro('mysqlCreateDatabaseIfNotExists', function (string|Stringable $database): bool {
            /** @var \Illuminate\Database\MySqlBuilder $this */
            if ($this->mysqlDatabaseExists($database)) {
                return false;
            }

            return $this->createDatabase($database);
        });

        MySqlBuilder::macro('mysqlTrashDatabase', function (string|Stringable $database) {
            $database = (string) $database;

            /** @var \Illuminate\Database\MySqlBuilder $this */
            if (! $this->mysqlDatabaseExists($database)) {
                return false;
            }

            try {

                $trashedAt = now()->format(config('schema-macros.trash-time-format'));
                $trashedDatabase = "trashed_{$trashedAt}_{$database}";
                $this->dropDatabaseIfExists($trashedDatabase);
                $this->createDatabase($trashedDatabase);

                $currentConnection = $this->getConnection();
                $currentConnectionName = $currentConnection->getName();

                config(['database.connections.new_connection_for_database' => array_merge(config("database.connections.{$currentConnectionName}"), [
                    'database' => $database,
                ])]);

                $db = DB::connection('new_connection_for_database');

                $db->statement("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';");

                $tables = $this->getTableListing();

                foreach ($tables as $table) {
                    $db->statement("create table if not exists `{$trashedDatabase}`.`{$table}` like `{$database}`.`{$table}`;");
                    $db->statement("insert into `{$trashedDatabase}`.`{$table}` select * from `{$database}`.`{$table}`;");
                }

                $this->dropDatabaseIfExists($database);
                $this->setConnection($currentConnection);

                return $trashedDatabase;
            } catch (\Exception $e) {
                return false;
            }
        });

        MySqlBuilder::macro('mysqlEmptyTrash', function (int $daysOld = 0, $debug = false) {
            /** @var \Illuminate\Database\MySqlBuilder $this */
            $trashDatabases = collect($this->getConnection()->select("SHOW DATABASES LIKE 'trashed_%'"))
                ->map(fn ($database) => array_values(get_object_vars($database))[0]);

            $deleted = 0;

            foreach ($trashDatabases as $trashDatabase) {

                if ($daysOld > 0) {
                    $dateSlice = array_slice(explode('_', $trashDatabase), 1, 6);
                    $dateString = implode('_', $dateSlice);
                    $date = Carbon::createFromFormat(config('schema-macros.trash-time-format'), $dateString)->getTimestamp();

                    if ($date > now()->subDays($daysOld)->getTimestamp()) {
                        continue;
                    }
                }

                if ($this->dropDatabaseIfExists($trashDatabase)) {
                    $deleted++;
                }
            }

            return $deleted;
        });
    }

    protected function bootSQLiteMacros()
    {
        SQLiteBuilder::macro('databaseExists', function (string|Stringable $database) {
            if ($this instanceof MySqlBuilder) {
                return $this->mysqlDatabaseExists($database);
            }

            return File::exists($database);
        });

        SQLiteBuilder::macro('createDatabaseIfNotExists', function (string|Stringable $database, bool $recursive = true) {
            if ($this instanceof MySqlBuilder) {
                return $this->mysqlCreateDatabaseIfNotExists($database);
            }

            if (! $this instanceof SQLiteBuilder) {
                throw new \Exception('This macro is only available for SQLite and MySQL databases');
            }

            if (File::exists($database)) {
                return false;
            }

            $directory = dirname($database);

            if (! File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            if (! File::exists($database)) {

                /** @var \Illuminate\Database\SQLiteBuilder $this */
                return $this->createDatabase($database);
            }

            return true;
        });

        SQLiteBuilder::macro('createDatabaseIfNotExistsRecursive', function (string|Stringable $database) {
            if (File::exists($database)) {
                return false;
            }

            /** @var \Illuminate\Database\SQLiteBuilder $this */
            return $this->createDatabaseIfNotExists($database, true);

            return true;
        });

        SQLiteBuilder::macro('trashDatabase', function (string|Stringable $database, string $trashDisk = 'local') {
            if ($this instanceof MySqlBuilder) {
                return $this->mysqlTrashDatabase($database);
            }

            if (! $this instanceof SQLiteBuilder) {
                throw new \Exception('This macro is only available for SQLite and MySQL databases');
            }

            if (! File::exists($database)
                || ! File::isFile($database)
                || ! File::isWritable($database)
            ) {
                return false;
            }

            $trashPath = Storage::disk($trashDisk)->path('.trash');

            if (! File::isDirectory($trashPath)) {
                File::makeDirectory($trashPath, 0755, true);
            }

            $trashFile = $trashPath.DIRECTORY_SEPARATOR.now()->format(config('schema-macros.trash-time-format')).'_'.basename($database);

            $moved = File::move($database, $trashFile);

            return $moved ? $trashFile : false;
        });

        SQLiteBuilder::macro('emptyTrash', function (int $daysOld = 0, string $trashDisk = 'local') {
            if ($this instanceof MySqlBuilder) {
                return $this->mysqlEmptyTrash($daysOld);
            }

            if (! $this instanceof SQLiteBuilder) {
                throw new \Exception('This macro is only available for SQLite and MySQL databases');
            }

            $trashPath = Storage::disk($trashDisk)->path('.trash');

            if (! File::isDirectory($trashPath)) {
                return false;
            }

            $files = File::files($trashPath);

            $deleted = 0;

            collect($files)->each(function ($file) use ($daysOld, &$deleted) {
                if ($daysOld > 0 && File::lastModified($file) > now()->subDays($daysOld)->getTimestamp()) {
                    return;
                }

                $deleted += File::delete($file);
            });

            return $deleted;
        });
    }
}
