<?php

namespace Envor\SchemaMacros\SQLite;

use Envor\SchemaMacros\SchemaMacros;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Stringable;

/**
 * Move the database to the trash
 *
 * @param  string|Stringable  $database
 *
 * @mixin \Illuminate\Database\Schema\SQLiteBuilder
 *
 * @return bool|string
 */
class SQLiteTrashDatabase
{
    public function __invoke(): callable
    {
        return function (string|Stringable $database, string $trashDisk = 'local'): bool|string {
            $database = (string) $database;

            if (! File::exists($database)
                || ! File::isFile($database)
                || ! File::isWritable($database)
            ) {
                return false;
            }

            $trashPath = Storage::disk($trashDisk)->path('.trash');

            if (! File::isDirectory($trashPath)) {
                File::makeDirectory($trashPath, 0755, true);
            }

            $trashFile = $trashPath.DIRECTORY_SEPARATOR.now()->format(SchemaMacros::TRASH_DATE_FORMAT).'_'.basename($database);

            $moved = File::move($database, $trashFile);

            return $moved ? $trashFile : false;
        };
    }
}
