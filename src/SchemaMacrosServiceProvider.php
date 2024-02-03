<?php

namespace Envor\SchemaMacros;

use Envor\SchemaMacros\Commands\SchemaMacrosCommand;
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
            ->name('laravel-schema-macros')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-schema-macros_table')
            ->hasCommand(SchemaMacrosCommand::class);
    }
}
