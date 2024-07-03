<?php

namespace Envor\SchemaMacros\PgSql;

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
class PgSqlCreateDatabaseIfNotExists
{
    public function __invoke(): callable
    {
        return function (string|Stringable $database): bool {
            $database = (string) $database;

            /** @var \Illuminate\Database\Schema\PostgresBuilder $this */
            if ($this->databaseExists($database)) {
                return false;
            }

            return $this->createDatabase($database);
        };
    }
}
