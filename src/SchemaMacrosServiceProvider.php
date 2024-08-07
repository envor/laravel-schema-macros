<?php

namespace Envor\SchemaMacros;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->name('laravel-schema-macros');
    }

    public function packageRegistered(): void
    {
        SchemaMacros::registerMacrosUsing($this->macros());
        SchemaMacros::registerMacros();
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function macros(): array
    {
        return [
            'databaseExists' => [
                'sqlite' => \Envor\SchemaMacros\SQLite\SQLiteDatabaseExists::class,
                'mysql' => \Envor\SchemaMacros\MySql\MySqlDatabaseExists::class,
                'mariadb' => \Envor\SchemaMacros\MariaDb\MariaDbDatabaseExists::class,
                'pgsql' => \Envor\SchemaMacros\PgSql\PgSqlDatabaseExists::class,
            ],
            'createDatabaseIfNotExists' => [
                'sqlite' => \Envor\SchemaMacros\SQLite\SQLiteCreateDatabaseIfNotExists::class,
                'mysql' => \Envor\SchemaMacros\MySql\MySqlCreateDatabaseIfNotExists::class,
                'mariadb' => \Envor\SchemaMacros\MariaDb\MariaDbCreateDatabaseIfNotExists::class,
                'pgsql' => \Envor\SchemaMacros\PgSql\PgSqlCreateDatabaseIfNotExists::class,
            ],
            'trashDatabase' => [
                'sqlite' => \Envor\SchemaMacros\SQLite\SQLiteTrashDatabase::class,
                'mysql' => \Envor\SchemaMacros\MySql\MySqlTrashDatabase::class,
                'mariadb' => \Envor\SchemaMacros\MariaDb\MariaDbTrashDatabase::class,
            ],
            'emptyTrash' => [
                'sqlite' => \Envor\SchemaMacros\SQLite\SQLiteEmptyTrash::class,
                'mysql' => \Envor\SchemaMacros\MySql\MySqlEmptyTrash::class,
                'mariadb' => \Envor\SchemaMacros\MariaDb\MariaDbEmptyTrash::class,
            ],
            'copyTable' => [
                'sqlite' => \Envor\SchemaMacros\SQLite\SQLiteCopyTable::class,
                'mysql' => \Envor\SchemaMacros\MySql\MySqlCopyTable::class,
                'mariadb' => \Envor\SchemaMacros\MariaDb\MariaDbCopyTable::class,
            ],
        ];
    }
}
