<?php

namespace SiteSource\PolymorphicSettings\Filament\Internal;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;

/**
 * @internal
 *
 * Walks a Filament schema/form container (a `Filament\Forms\Form` in
 * Filament 3, a `Filament\Schemas\Schema` in Filament 4 and 5) and
 * extracts:
 *   - the names of every field (used to load values from the settings
 *     store at mount time)
 *   - the names of TextInput fields marked ->password() (used at save
 *     time to persist with encrypted: true)
 *
 * Container parameters are intentionally untyped so the same code path
 * works across all supported Filament majors. Both Form and Schema
 * expose the same `getComponents(withHidden: ...)` method, and both
 * trees of components expose `getChildComponents()` and (for fields)
 * `getName()`. `Filament\Forms\Components\Field` and
 * `Filament\Forms\Components\TextInput` live at the same FQN in every
 * supported version.
 */
final class FormIntrospector
{
    /**
     * @return array<int, string>
     */
    public static function collectFieldNames(mixed $container): array
    {
        $names = [];
        foreach ($container->getComponents(withHidden: true) as $component) {
            self::walkForFieldNames($component, $names);
        }

        return array_values(array_unique($names));
    }

    /**
     * @return array<int, string>
     */
    public static function detectEncryptedFields(mixed $container): array
    {
        $encrypted = [];
        foreach ($container->getComponents(withHidden: true) as $component) {
            self::walkForEncryptedFields($component, $encrypted);
        }

        return array_values(array_unique($encrypted));
    }

    /**
     * @param  array<int, string>  $names
     */
    private static function walkForFieldNames(mixed $component, array &$names): void
    {
        if ($component instanceof Field) {
            $names[] = $component->getName();
        }

        foreach ($component->getChildComponents() as $child) {
            self::walkForFieldNames($child, $names);
        }
    }

    /**
     * @param  array<int, string>  $encrypted
     */
    private static function walkForEncryptedFields(mixed $component, array &$encrypted): void
    {
        if ($component instanceof TextInput && $component->isPassword()) {
            $encrypted[] = $component->getName();
        }

        foreach ($component->getChildComponents() as $child) {
            self::walkForEncryptedFields($child, $encrypted);
        }
    }
}
