<?php

namespace Envor\SchemaMacros\Macros\MySql;

use Stringable;

/**
 * Create the database if it does not exist
 *
 * @param  string|Stringable  $database
 *
 * @mixin \Illuminate\Database\Schema\MySqlBuilder
 *
 * @return bool
 */
class MySqlCreateDatabaseIfNotExists
{
    public function __invoke(): callable
    {
        return function (string|Stringable $database): bool {
            $database = (string) $database;

            /** @var \Illuminate\Database\Schema\MySqlBuilder $this */
            if ($this->mysqlDatabaseExists($database)) {
                return false;
            }

            return $this->createDatabase($database);
        };
    }
}
