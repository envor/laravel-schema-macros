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

    public function packageRegistered()
    {
        SchemaMacros::registerMacrosUsing($this->macros());

        SchemaMacros::registerMacros();
    }

    private function macros(): array
    {
        return [
            'databaseExists' => [
                'sqlite' => \Envor\SchemaMacros\SQLite\SQLiteDatabaseExists::class,
                'mysql' => \Envor\SchemaMacros\MySql\MySqlDatabaseExists::class,
            ],
            'createDatabaseIfNotExists' => [
                'sqlite' => \Envor\SchemaMacros\SQLite\SQLiteCreateDatabaseIfNotExists::class,
                'mysql' => \Envor\SchemaMacros\MySql\MySqlCreateDatabaseIfNotExists::class,
            ],
            'trashDatabase' => [
                'sqlite' => \Envor\SchemaMacros\SQLite\SQLiteTrashDatabase::class,
                'mysql' => \Envor\SchemaMacros\MySql\MySqlTrashDatabase::class,
            ],
            'emptyTrash' => [
                'sqlite' => \Envor\SchemaMacros\SQLite\SQLiteEmptyTrash::class,
                'mysql' => \Envor\SchemaMacros\MySql\MySqlEmptyTrash::class,
            ],
        ];
    }
}
