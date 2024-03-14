<?php

namespace Envor\SchemaMacros\MariaDb;

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
class MariaDbDatabaseExists
{
    public function __invoke(): callable
    {
        return function (string|Stringable $database): bool {
            $database = (string) $database;

            /** @var \Illuminate\Database\Schema\MariaDbBuilder $this */
            return (bool) $this->getConnection()->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$database}'");
        };
    }
}
