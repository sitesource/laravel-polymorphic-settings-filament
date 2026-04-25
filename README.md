# Filament integration for laravel-polymorphic-settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sitesource/laravel-polymorphic-settings-filament.svg?style=flat-square)](https://packagist.org/packages/sitesource/laravel-polymorphic-settings-filament)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/sitesource/laravel-polymorphic-settings-filament/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/sitesource/laravel-polymorphic-settings-filament/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/sitesource/laravel-polymorphic-settings-filament.svg?style=flat-square)](https://packagist.org/packages/sitesource/laravel-polymorphic-settings-filament)

A base [Filament](https://filamentphp.com) page class and helper trait that auto-bind your form schema to [`sitesource/laravel-polymorphic-settings`](https://github.com/sitesource/laravel-polymorphic-settings). Stop hand-rolling `mount()` / `save()` plumbing on every settings page.

```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use SiteSource\PolymorphicSettings\Filament\Pages\PolymorphicSettingsPage;

class CommerceSettings extends PolymorphicSettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static string $view = 'filament.pages.commerce-settings';

    public function form(Form $form): Form
    {
        return $form->schema([
            Toggle::make('commerce.stripe.enabled'),
            TextInput::make('commerce.stripe.public_key'),
            TextInput::make('commerce.stripe.secret_key')->password(),  // auto-encrypts
        ])->statePath('data');
    }
}
```

That's it. Mount pulls every field's current value from the settings store. Save persists every field back, encrypting any `TextInput->password()` field at rest. No `mount()` or `save()` to write yourself.

## Installation

```bash
composer require sitesource/laravel-polymorphic-settings-filament
```

You'll also need the core package installed and migrated:

```bash
php artisan polymorphic-settings:install
```

Filament 3.x is required. Filament 4.x support will land in a 0.2 release once Filament 4 is stable.

## Usage

### Global settings page

The default scope is global — settings live alongside `PolymorphicSettings::global()` reads/writes:

```php
class GeneralSettings extends PolymorphicSettingsPage
{
    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('site_title'),
            TextInput::make('contact_email'),
        ])->statePath('data');
    }
}
```

### Scoped to a model (per-team, per-tenant, per-user)

Override `scopeFor()` to return the model whose settings you're editing:

```php
class TeamSettings extends PolymorphicSettingsPage
{
    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('theme.primary_color'),
            TextInput::make('theme.logo_url'),
        ])->statePath('data');
    }

    protected function scopeFor(): ?Model
    {
        return auth()->user()->currentTeam;
    }
}
```

### Encryption

Any `TextInput` marked with `->password()` is automatically persisted as `encrypted: true`. The Filament form continues to work normally — the user types a plaintext value, the page encrypts on save, and the next mount decrypts transparently.

```php
TextInput::make('stripe.secret_key')->password()
```

### Customising the post-save UX

Default behaviour is a Filament success notification. Override `onSaved()` to do anything else.

```php
protected function onSaved(): void
{
    parent::onSaved();
    Cache::tags('navigation')->flush();
}
```

## Trait variant: `BindsToSettings`

If you want to keep your own `Page` base (or use this on any Livewire component, not just Filament pages), use the trait:

```php
use SiteSource\PolymorphicSettings\Filament\Concerns\BindsToSettings;

class MyPage extends Page
{
    use BindsToSettings;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->fillFromSettings([
            'commerce.stripe.enabled',
            'commerce.stripe.public_key',
            'commerce.stripe.secret_key',
        ]));
    }

    public function save(): void
    {
        $this->persistToSettings(
            $this->form->getState(),
            encryptedKeys: ['commerce.stripe.secret_key'],
        );
    }

    protected function settingsScope(): ?Model
    {
        return null;
    }
}
```

The trait gives you `fillFromSettings(array $keys): array` and `persistToSettings(array $values, array $encryptedKeys = []): void`. You own the form schema and the lifecycle — the trait just bulk-reads and bulk-writes the store.

## Testing

```bash
composer test
```

13 tests, 23 assertions. PHPStan level 5 + Pint clean.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Credits

- [Nathan Call](https://github.com/nathancall)

## License

MIT — see [LICENSE.md](LICENSE.md).
