<?php

namespace Envor\SchemaMacros;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;

class SchemaMacros
{
    /**
     * The date format used for the trash
     *
     * @var string
     */
    public const TRASH_DATE_FORMAT = 'Y-m-d_H-i-s';

    /**
     * Get the macros and their supported <drivers, builders>
     *
     * @return array<string, array<string, string>>
     */
    public static function macros(): array
    {
        return app()->make(SchemaMacrosCollection::class)->toArray();
    }

    public static function registerMacros()
    {
        return Collection::make(static::macros())
            ->keys()
            ->reject(fn ($macro) => Builder::hasMacro($macro))
            ->each(fn ($macro) => Builder::macro($macro, fn (...$args) => SchemaMacros::registerMacro($macro, $this, ...$args)));
    }

    public static function registerMacrosUsing(array $macros)
    {
        return app()->singleton(SchemaMacrosCollection::class, fn () => new SchemaMacrosCollection($macros));
    }

    public static function supportedDrivers($macro): array
    {
        return array_keys(static::macros()[$macro]);
    }

    protected static function validDriver(string $macro, $driver): bool
    {
        return in_array($driver, static::supportedDrivers($macro));
    }

    protected static function ensureDriverIsSupported(string $macro, $builder): bool
    {
        if (! static::validDriver($macro, $driver = $builder->getConnection()->getDriverName())) {
            $supportedDrivers = implode(', ', static::supportedDrivers($macro));
            throw new UnsupportedDriver("The {$macro}() macro does not support {$driver}. Supported drivers are: {$supportedDrivers}");
        }

        return true;
    }

    public static function registerMacro($macro, $builder, ...$args)
    {

        static::ensureDriverIsSupported($macro, $builder);

        return Collection::make(static::supportedDrivers($macro))
            ->filter(fn ($supportedDriver) => $builder->getConnection()->getDriverName() === $supportedDriver)
            ->map(fn ($driver) => static::getMacroForDriver($driver, $macro, $builder, ...$args))
            ->first();
    }

    protected static function getMacroForDriver($driver, $macro, $builder, ...$args)
    {
        static::registerMacroForDriver($driver, $macro);

        return $builder->{$driver.ucfirst($macro)}(...$args);
    }

    protected static function registerMacroForDriver($driver, $macro): void
    {
        Builder::macro($driver.ucfirst($macro), app(static::macros()[$macro][$driver])());
    }
}
