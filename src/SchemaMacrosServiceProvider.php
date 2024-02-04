<?php

namespace Envor\SchemaMacros;

use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Support\Collection;
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

    public function packageBooted(): void
    {
        Collection::make($this->sqliteBuilderMacros())
            ->reject(fn ($class, $macro) => Builder::hasMacro($macro))
            ->each(fn ($class, $macro) => Builder::macro($macro, app($class)()));

        Collection::make($this->mysqlBuilderMacros())
            ->reject(fn ($class, $macro) => Builder::hasMacro($macro))
            ->each(fn ($class, $macro) => Builder::macro($macro, app($class)()));

        Collection::make($this->builderMacros())
            ->reject(fn ($macro, $name) => Builder::hasMacro($name))
            ->each(fn ($macro, $name) => Builder::macro($name, $macro));
    }

    /**
     * @return array<string, callable>
     */
    private function builderMacros(): array
    {
        return [
            'databaseExists' => function (string|Stringable $database) {
                $database = (string) $database;

                if ($this instanceof MySqlBuilder) {
                    return $this->mysqlDatabaseExists($database);
                }

                if ($this instanceof SQLiteBuilder) {
                    return $this->sqliteDatabaseExists($database);
                }

                throw new \Exception('The databaseExists() macro does not support'.get_class($this));
            },
            'createDatabaseIfNotExists' => function (string|Stringable $database, bool $recursive = true) {
                $database = (string) $database;

                if ($this instanceof MySqlBuilder) {
                    return $this->mysqlCreateDatabaseIfNotExists($database);
                }

                if ($this instanceof SQLiteBuilder) {
                    return $this->sqliteCreateDatabaseIfNotExists($database, $recursive);
                }

                throw new \Exception('The createDatabaseIfNotExists() macro does not support'.get_class($this));
            },
            'trashDatabase' => function (string|Stringable $database, string $trashDisk = 'local') {
                $database = (string) $database;

                if ($this instanceof MySqlBuilder) {
                    return $this->mysqlTrashDatabase($database);
                }

                if ($this instanceof SQLiteBuilder) {
                    return $this->sqliteTrashDatabase($database, $trashDisk);
                }

                throw new \Exception('The trashDatabase() macro does not support'.get_class($this));
            },
            'emptyTrash' => function (int $daysOld = 0, string $trashDisk = 'local') {
                if ($this instanceof MySqlBuilder) {
                    return $this->mysqlEmptyTrash($daysOld);
                }

                if ($this instanceof SQLiteBuilder) {
                    return $this->sqliteEmptyTrash($daysOld, $trashDisk);
                }

                throw new \Exception('The emptyTrash() macro does not support'.get_class($this));
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sqliteBuilderMacros(): array
    {
        return [
            'sqliteDatabaseExists' => \Envor\SchemaMacros\SQLite\SQLiteDatabaseExists::class,
            'sqliteCreateDatabaseIfNotExists' => \Envor\SchemaMacros\SQLite\SQLiteCreateDatabaseIfNotExists::class,
            'sqliteTrashDatabase' => \Envor\SchemaMacros\SQLite\SQLiteTrashDatabase::class,
            'sqliteEmptyTrash' => \Envor\SchemaMacros\SQLite\SQLiteEmptyTrash::class,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function mysqlBuilderMacros(): array
    {
        return [
            'mysqlDatabaseExists' => \Envor\SchemaMacros\MySql\MySqlDatabaseExists::class,
            'mysqlCreateDatabaseIfNotExists' => \Envor\SchemaMacros\MySql\MySqlCreateDatabaseIfNotExists::class,
            'mysqlTrashDatabase' => \Envor\SchemaMacros\MySql\MySqlTrashDatabase::class,
            'mysqlEmptyTrash' => \Envor\SchemaMacros\MySql\MySqlEmptyTrash::class,
        ];
    }
}
