<?php

namespace Envor\SchemaMacros\MySql;

use Stringable;

/**
 * determine if the database exists
 *
 * @param  string|Stringable  $database
 *
 * @mixin \Illuminate\Database\Schema\MySqlBuilder
 *
 * @return bool
 */
class MySqlDatabaseExists
{
    public function __invoke(): callable
    {
        return function (string|Stringable $database): bool {
            $database = (string) $database;

            /** @var \Illuminate\Database\Schema\MySqlBuilder $this */
            return (bool) $this->getConnection()->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$database}'");
        };
    }
}
