<?php

namespace Envor\SchemaMacros\MySql;

use Envor\SchemaMacros\SchemaMacros;
use Illuminate\Support\Carbon;

/**
 * Erase trashed databases
 *
 * @param  int  $daysOld
 *
 * @mixin \Illuminate\Database\Schema\MySqlBuilder
 *
 * @return int
 */
class MySqlEmptyTrash
{
    public function __invoke(): callable
    {
        return function (int $daysOld = 0): int {
            /** @var \Illuminate\Database\Schema\MySqlBuilder $this */
            $trashDatabases = collect($this->getConnection()->select("SHOW DATABASES LIKE 'trashed_%'"))
                ->map(fn ($database) => array_values(get_object_vars($database))[0]);

            $deleted = 0;

            foreach ($trashDatabases as $trashDatabase) {
                $trashDatabase = (string) $trashDatabase;

                if ($daysOld > 0) {
                    $dateSlice = array_slice(explode('_', $trashDatabase), 1, 6);
                    $dateString = implode('_', $dateSlice);
                    $date = Carbon::createFromFormat(SchemaMacros::TRASH_DATE_FORMAT, $dateString)->getTimestamp();

                    if ($date > now()->subDays($daysOld)->getTimestamp()) {
                        continue;
                    }
                }

                if ($this->dropDatabaseIfExists($trashDatabase)) {
                    $deleted++;
                }
            }

            return $deleted;
        };
    }
}
