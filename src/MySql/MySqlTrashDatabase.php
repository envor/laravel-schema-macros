<?php

namespace Envor\SchemaMacros\MySql;

use Envor\SchemaMacros\SchemaMacros;
use Illuminate\Support\Facades\DB;
use Stringable;

/**
 * Move the database to the trash
 *
 * @param  string|Stringable  $database
 *
 * @mixin \Illuminate\Database\Schema\MySqlBuilder
 *
 * @return bool|string
 */
class MySqlTrashDatabase
{
    public function __invoke(): callable
    {
        return function (string|Stringable $database) {
            $database = (string) $database;

            /** @var \Illuminate\Database\Schema\MySqlBuilder $this */
            if (! $this->mysqlDatabaseExists($database)) {
                return false;
            }

            try {

                $trashedAt = now()->format(SchemaMacros::TRASH_DATE_FORMAT);
                $trashedDatabase = "trashed_{$trashedAt}_{$database}";
                $this->dropDatabaseIfExists($trashedDatabase);
                $this->createDatabase($trashedDatabase);

                $currentConnection = $this->getConnection();
                $currentConnectionName = $currentConnection->getName();

                config(['database.connections.new_connection_for_database' => array_merge(config("database.connections.{$currentConnectionName}"), [
                    'database' => $database,
                ])]);

                $db = DB::connection('new_connection_for_database');

                $db->statement("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';");

                $tables = $this->getTableListing();

                foreach ($tables as $table) {
                    $db->statement("create table if not exists `{$trashedDatabase}`.`{$table}` like `{$database}`.`{$table}`;");
                    $db->statement("insert into `{$trashedDatabase}`.`{$table}` select * from `{$database}`.`{$table}`;");
                }

                $this->dropDatabaseIfExists($database);
                $this->setConnection($currentConnection);

                return $trashedDatabase;
            } catch (\Exception $e) {
                return false;
            }
        };
    }
}
