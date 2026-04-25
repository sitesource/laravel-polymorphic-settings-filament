<?php

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use SiteSource\PolymorphicSettings\Filament\Internal\FormIntrospector;
use SiteSource\PolymorphicSettings\Filament\Tests\Support\FormOwner;

/**
 * Build a real Filament form schema rooted in a Form instance. We can't
 * trivially construct a Form without a Livewire owner in some Filament
 * versions, so test pages use a tiny stub component as the owner.
 */
function makeForm(array $schema): Form
{
    $owner = new FormOwner;

    return Form::make($owner)->schema($schema);
}

it('collects all top-level field names', function () {
    $form = makeForm([
        Toggle::make('commerce.stripe.enabled'),
        TextInput::make('site_title'),
        TextInput::make('commerce.stripe.public_key'),
    ]);

    expect(FormIntrospector::collectFieldNames($form))
        ->toBe([
            'commerce.stripe.enabled',
            'site_title',
            'commerce.stripe.public_key',
        ]);
});

it('walks into container components and collects nested fields', function () {
    $form = makeForm([
        Section::make('Stripe')->schema([
            Toggle::make('commerce.stripe.enabled'),
            TextInput::make('commerce.stripe.public_key'),
        ]),
        Section::make('Branding')->schema([
            TextInput::make('site_title'),
        ]),
    ]);

    expect(FormIntrospector::collectFieldNames($form))
        ->toContain('commerce.stripe.enabled')
        ->toContain('commerce.stripe.public_key')
        ->toContain('site_title')
        ->toHaveCount(3);
});

it('detects no encrypted fields when none are marked password', function () {
    $form = makeForm([
        Toggle::make('commerce.stripe.enabled'),
        TextInput::make('site_title'),
    ]);

    expect(FormIntrospector::detectEncryptedFields($form))->toBe([]);
});

it('detects TextInput fields marked password as encrypted', function () {
    $form = makeForm([
        TextInput::make('commerce.stripe.public_key'),
        TextInput::make('commerce.stripe.secret_key')->password(),
        TextInput::make('site_title'),
    ]);

    expect(FormIntrospector::detectEncryptedFields($form))
        ->toBe(['commerce.stripe.secret_key']);
});

it('detects nested password fields inside containers', function () {
    $form = makeForm([
        Section::make('Stripe')->schema([
            TextInput::make('commerce.stripe.secret_key')->password(),
            TextInput::make('commerce.stripe.public_key'),
        ]),
    ]);

    expect(FormIntrospector::detectEncryptedFields($form))
        ->toBe(['commerce.stripe.secret_key']);
});

it('does not treat non-TextInput components as encryptable', function () {
    // Toggle, Select, etc. cannot be marked ->password() in Filament 3,
    // and even if they could, we only consider TextInput.
    $form = makeForm([
        Toggle::make('boolean_secret'),
        TextInput::make('text_secret')->password(),
    ]);

    expect(FormIntrospector::detectEncryptedFields($form))
        ->toBe(['text_secret']);
});
