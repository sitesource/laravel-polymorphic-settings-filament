<?php

namespace SiteSource\PolymorphicSettings\Filament\Tests\Support;

/**
 * Tiny version-detection helper used by tests to construct the right
 * schema container and to import the right Section namespace. Uses
 * string class names for the version-specific symbols so static
 * analysis does not need to resolve a class that is only present in
 * the *other* installed Filament major.
 */
final class FilamentVersion
{
    public static function isV4OrNewer(): bool
    {
        return class_exists('Filament\Schemas\Schema');
    }

    /**
     * Build a Filament 3 Form OR a Filament 4+ Schema, depending on
     * which is installed. Returns whichever container the introspector
     * accepts.
     *
     * @param  array<int, object>  $components
     */
    public static function makeContainer(FormOwner $owner, array $components): mixed
    {
        if (self::isV4OrNewer()) {
            $class = 'Filament\Schemas\Schema';

            return $class::make($owner)->components($components);
        }

        $class = 'Filament\Forms\Form';

        return $class::make($owner)->schema($components);
    }

    /**
     * FQN of the Section component for the installed Filament version.
     * v3 had it under Forms\Components; v4 moved it to Schemas\Components.
     *
     * @return class-string
     */
    public static function sectionClass(): string
    {
        return self::isV4OrNewer()
            ? 'Filament\Schemas\Components\Section'
            : 'Filament\Forms\Components\Section';
    }
}
