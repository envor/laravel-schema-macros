<?php

namespace Envor\SchemaMacros\SQLite;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Erase trashed databases
 *
 * @param  int  $daysOld
 * @param  string  $trashDisk
 *
 * @mixin \Illuminate\Database\Schema\SQLiteBuilder
 *
 * @return int|bool
 */
class SQLiteEmptyTrash
{
    public function __invoke(): callable
    {
        return function (int $daysOld = 0, $trashDisk = 'local'): int|bool {
            $trashPath = Storage::disk($trashDisk)->path('.trash');

            if (! File::isDirectory($trashPath)) {
                return false;
            }

            $files = File::files($trashPath);

            $deleted = 0;

            collect($files)->each(function ($file) use ($daysOld, &$deleted) {
                if ($daysOld > 0 && File::lastModified($file) > now()->subDays($daysOld)->getTimestamp()) {
                    return;
                }

                $deleted += File::delete($file);
            });

            return $deleted;
        };
    }
}
