<?php

namespace Envor\SchemaMacros\Macros\SQLite;

use Illuminate\Support\Facades\File;
use Stringable;

/**
 * Create the database if it does not exist
 *
 * @param  string|Stringable  $database
 * @param  bool  $recursive  = true
 *
 * @mixin \Illuminate\Database\Schema\SQLiteBuilder
 *
 * @return bool
 */
class SQLiteCreateDatabaseIfNotExists
{
    public function __invoke(): callable
    {
        return function (string|Stringable $database, bool $recursive = true): bool {
            $database = (string) $database;

            if (File::exists($database)) {
                return false;
            }

            $directory = dirname($database);

            if (! File::isDirectory($directory) && $recursive) {
                File::makeDirectory($directory, 0755, true);
            }

            if (! File::exists($database)) {

                /** @var \Illuminate\Database\Schema\SQLiteBuilder $this */
                return $this->createDatabase($database);
            }

            return false;
        };
    }
}
