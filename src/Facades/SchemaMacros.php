<?php

namespace Envor\SchemaMacros\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Envor\SchemaMacros\SchemaMacros
 */
class SchemaMacros extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Envor\SchemaMacros\SchemaMacros::class;
    }
}
