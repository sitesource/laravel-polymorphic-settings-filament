<?php

namespace SiteSource\PolymorphicSettings\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use SiteSource\PolymorphicSettings\Facades\PolymorphicSettings;
use SiteSource\PolymorphicSettings\Filament\Internal\FormIntrospector;
use SiteSource\PolymorphicSettings\SettingsStore;

/**
 * @property Form $form
 *
 * Base class for Filament pages that read and write settings through
 * sitesource/laravel-polymorphic-settings.
 *
 * Subclass and define your form schema. The page handles mount-time
 * load and save-time persist for you, including auto-encrypting any
 * `TextInput` field marked with `->password()`.
 *
 * Example:
 *
 *     use Filament\Forms\Components\TextInput;
 *     use Filament\Forms\Components\Toggle;
 *     use Filament\Forms\Form;
 *     use SiteSource\PolymorphicSettings\Filament\Pages\PolymorphicSettingsPage;
 *
 *     class CommerceSettings extends PolymorphicSettingsPage
 *     {
 *         protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
 *         protected static string $view = 'filament.pages.commerce-settings';
 *
 *         public function form(Form $form): Form
 *         {
 *             return $form->schema([
 *                 Toggle::make('commerce.stripe.enabled'),
 *                 TextInput::make('commerce.stripe.public_key'),
 *                 TextInput::make('commerce.stripe.secret_key')->password(),
 *             ])->statePath('data');
 *         }
 *     }
 *
 * Override `scopeFor()` to bind the page to a specific model — e.g. to
 * edit the current team's settings rather than the global ones.
 */
abstract class PolymorphicSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->loadFromSettings());
    }

    /**
     * Override to scope settings to a model. Return null (default) for
     * global settings.
     */
    protected function scopeFor(): ?Model
    {
        return null;
    }

    public function save(): void
    {
        $values = $this->form->getState();
        $store = $this->resolveStore();
        $encryptedKeys = FormIntrospector::detectEncryptedFields($this->form);

        foreach ($values as $key => $value) {
            $store->put(
                $key,
                $value,
                encrypted: in_array($key, $encryptedKeys, true),
            );
        }

        $this->onSaved();
    }

    /**
     * Pull every form field's current setting value from the store.
     *
     * @return array<string, mixed>
     */
    protected function loadFromSettings(): array
    {
        $store = $this->resolveStore();
        $values = [];

        foreach (FormIntrospector::collectFieldNames($this->form) as $name) {
            $values[$name] = $store->get($name);
        }

        return $values;
    }

    /**
     * Hook for subclasses to customise the post-save UX. Default emits a
     * Filament success notification.
     */
    protected function onSaved(): void
    {
        Notification::make()
            ->title(__('Settings saved'))
            ->success()
            ->send();
    }

    private function resolveStore(): SettingsStore
    {
        $scope = $this->scopeFor();

        return $scope instanceof Model
            ? PolymorphicSettings::for($scope)
            : PolymorphicSettings::global();
    }
}
