<?php

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use SiteSource\PolymorphicSettings\Filament\Internal\FormIntrospector;
use SiteSource\PolymorphicSettings\Filament\Tests\Support\FilamentVersion;
use SiteSource\PolymorphicSettings\Filament\Tests\Support\FormOwner;

/**
 * Constructs a Filament container appropriate for the installed major:
 *   - v3:    Filament\Forms\Form::make($owner)->schema($components)
 *   - v4/5:  Filament\Schemas\Schema::make($owner)->components($components)
 *
 * The introspector under test accepts either by duck-typing.
 */
function makeContainer(array $components): mixed
{
    return FilamentVersion::makeContainer(new FormOwner, $components);
}

it('collects all top-level field names', function () {
    $container = makeContainer([
        Toggle::make('commerce.stripe.enabled'),
        TextInput::make('site_title'),
        TextInput::make('commerce.stripe.public_key'),
    ]);

    expect(FormIntrospector::collectFieldNames($container))
        ->toBe([
            'commerce.stripe.enabled',
            'site_title',
            'commerce.stripe.public_key',
        ]);
});

it('walks into container components and collects nested fields', function () {
    $section = FilamentVersion::sectionClass();

    $container = makeContainer([
        $section::make('Stripe')->{FilamentVersion::isV4OrNewer() ? 'components' : 'schema'}([
            Toggle::make('commerce.stripe.enabled'),
            TextInput::make('commerce.stripe.public_key'),
        ]),
        $section::make('Branding')->{FilamentVersion::isV4OrNewer() ? 'components' : 'schema'}([
            TextInput::make('site_title'),
        ]),
    ]);

    expect(FormIntrospector::collectFieldNames($container))
        ->toContain('commerce.stripe.enabled')
        ->toContain('commerce.stripe.public_key')
        ->toContain('site_title')
        ->toHaveCount(3);
});

it('detects no encrypted fields when none are marked password', function () {
    $container = makeContainer([
        Toggle::make('commerce.stripe.enabled'),
        TextInput::make('site_title'),
    ]);

    expect(FormIntrospector::detectEncryptedFields($container))->toBe([]);
});

it('detects TextInput fields marked password as encrypted', function () {
    $container = makeContainer([
        TextInput::make('commerce.stripe.public_key'),
        TextInput::make('commerce.stripe.secret_key')->password(),
        TextInput::make('site_title'),
    ]);

    expect(FormIntrospector::detectEncryptedFields($container))
        ->toBe(['commerce.stripe.secret_key']);
});

it('detects nested password fields inside containers', function () {
    $section = FilamentVersion::sectionClass();
    $childMethod = FilamentVersion::isV4OrNewer() ? 'components' : 'schema';

    $container = makeContainer([
        $section::make('Stripe')->{$childMethod}([
            TextInput::make('commerce.stripe.secret_key')->password(),
            TextInput::make('commerce.stripe.public_key'),
        ]),
    ]);

    expect(FormIntrospector::detectEncryptedFields($container))
        ->toBe(['commerce.stripe.secret_key']);
});

it('does not treat non-TextInput components as encryptable', function () {
    $container = makeContainer([
        Toggle::make('boolean_secret'),
        TextInput::make('text_secret')->password(),
    ]);

    expect(FormIntrospector::detectEncryptedFields($container))
        ->toBe(['text_secret']);
});
