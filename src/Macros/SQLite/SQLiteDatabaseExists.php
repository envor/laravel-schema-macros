<?php

namespace Envor\SchemaMacros\Macros\SQLite;

use Illuminate\Support\Facades\File;
use Stringable;

/**
 * determine if the database exists
 *
 * @param  string|Stringable  $database
 *
 * @mixin \Illuminate\Database\Schema\SQLiteBuilder
 *
 * @return bool
 */
class SQLiteDatabaseExists
{
    public function __invoke(): callable
    {
        return function (string|Stringable $database): bool {
            $database = (string) $database;

            return File::exists($database);
        };
    }
}
