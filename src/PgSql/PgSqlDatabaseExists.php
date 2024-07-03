<?php

namespace Envor\SchemaMacros\PgSql;

use Stringable;

/**
 * determine if the database exists
 *
 * @param  string|Stringable  $database
 *
 * @mixin \Illuminate\Database\Schema\MariaDbBuilder
 *
 * @return bool
 */
class PgSqlDatabaseExists
{
    public function __invoke(): callable
    {
        return function (string|Stringable $database): bool {
            $database = (string) $database;

            /** @var \Illuminate\Database\Schema\PostgresBuilder $this */
            return (bool) $this->getConnection()->select("SELECT datname FROM pg_database WHERE datname = '{$database}'");
        };
    }
}
