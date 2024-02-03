<?php

namespace Envor\SchemaMacros\Commands;

use Illuminate\Console\Command;

class SchemaMacrosCommand extends Command
{
    public $signature = 'laravel-schema-macros';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
