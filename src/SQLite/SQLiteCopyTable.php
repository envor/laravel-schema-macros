<?php

namespace Envor\SchemaMacros\SQLite;

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
class SQLiteCopyTable
{
    public function __invoke(): callable
    {
        return function (string $from, ?string $to = null): mixed {
            $from = (string) $from;
            $to = $to ? (string) $to : $from.'_copy';

            $copy = function ($from, $to) use (&$copy) {
                /** @var \Illuminate\Database\Schema\SQLiteBuilder $this */
                if ($this->hasTable($to)) {
                    $to = $to.'_copy';

                    return $copy($from, $to);
                }

                /** @var \Illuminate\Database\Schema\SQLiteBuilder $this */
                return $this->getConnection()->statement("CREATE TABLE {$to} AS SELECT * FROM {$from}");
            };

            return $copy($from, $to);
        };
    }
}
