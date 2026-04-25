<?php

namespace SiteSource\PolymorphicSettings\Filament\Internal;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

/**
 * @internal
 *
 * Walks a Filament form schema and extracts:
 *   - the names of every field (used to load values from the settings store)
 *   - the names of fields that should be persisted as encrypted (currently:
 *     any TextInput marked ->password())
 *
 * Lives in an Internal namespace because the API is subject to change as
 * Filament evolves (Filament 4 will reorganise component namespaces).
 */
final class FormIntrospector
{
    /**
     * @return array<int, string>
     */
    public static function collectFieldNames(Form $form): array
    {
        $names = [];
        foreach ($form->getComponents(withHidden: true) as $component) {
            self::walkForFieldNames($component, $names);
        }

        return array_values(array_unique($names));
    }

    /**
     * @return array<int, string>
     */
    public static function detectEncryptedFields(Form $form): array
    {
        $encrypted = [];
        foreach ($form->getComponents(withHidden: true) as $component) {
            self::walkForEncryptedFields($component, $encrypted);
        }

        return array_values(array_unique($encrypted));
    }

    /**
     * @param  array<int, string>  $names
     */
    private static function walkForFieldNames(Component $component, array &$names): void
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
    private static function walkForEncryptedFields(Component $component, array &$encrypted): void
    {
        if ($component instanceof TextInput && $component->isPassword()) {
            $encrypted[] = $component->getName();
        }

        foreach ($component->getChildComponents() as $child) {
            self::walkForEncryptedFields($child, $encrypted);
        }
    }
}
