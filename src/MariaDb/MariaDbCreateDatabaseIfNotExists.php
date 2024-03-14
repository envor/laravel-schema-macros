<?php

namespace Envor\SchemaMacros\MariaDb;

use Stringable;

/**
 * Create the database if it does not exist
 *
 * @param  string|Stringable  $database
 *
 * @mixin \Illuminate\Database\Schema\MariaDbBuilder
 *
 * @return bool
 */
class MariaDbCreateDatabaseIfNotExists
{
    public function __invoke(): callable
    {
        return function (string|Stringable $database): bool {
            $database = (string) $database;

            /** @var \Illuminate\Database\Schema\MariaDbBuilder $this */
            if ($this->databaseExists($database)) {
                return false;
            }

            return $this->createDatabase($database);
        };
    }
}
