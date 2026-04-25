<?php

namespace SiteSource\PolymorphicSettings\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use SiteSource\PolymorphicSettings\Facades\PolymorphicSettings;
use SiteSource\PolymorphicSettings\SettingsStore;

/**
 * Helpers for Filament pages (or any Livewire component) that want to
 * read/write a fixed set of settings without extending
 * PolymorphicSettingsPage. The host owns the form schema and lifecycle —
 * this trait just provides bulk read/write helpers.
 *
 * Example:
 *
 *     class MyPage extends Page
 *     {
 *         use BindsToSettings;
 *
 *         protected function settingsScope(): mixed
 *         {
 *             return null; // null = global, return a Model for scoped
 *         }
 *
 *         public function mount(): void
 *         {
 *             $this->form->fill($this->fillFromSettings([
 *                 'commerce.stripe.enabled',
 *                 'commerce.stripe.public_key',
 *             ]));
 *         }
 *
 *         public function save(): void
 *         {
 *             $this->persistToSettings(
 *                 $this->form->getState(),
 *                 encryptedKeys: ['commerce.stripe.secret_key'],
 *             );
 *         }
 *     }
 */
trait BindsToSettings
{
    /**
     * Override to scope to a model. Return null for global settings.
     */
    protected function settingsScope(): ?Model
    {
        return null;
    }

    /**
     * Read each requested key from the configured scope.
     *
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    protected function fillFromSettings(array $keys): array
    {
        $store = $this->resolveSettingsStore();

        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $store->get($key);
        }

        return $values;
    }

    /**
     * Write each value to the configured scope, marking the listed keys
     * as encrypted at rest.
     *
     * @param  array<string, mixed>  $values
     * @param  array<int, string>  $encryptedKeys
     */
    protected function persistToSettings(array $values, array $encryptedKeys = []): void
    {
        $store = $this->resolveSettingsStore();

        foreach ($values as $key => $value) {
            $store->put(
                $key,
                $value,
                encrypted: in_array($key, $encryptedKeys, true),
            );
        }
    }

    private function resolveSettingsStore(): SettingsStore
    {
        $scope = $this->settingsScope();

        return $scope instanceof Model
            ? PolymorphicSettings::for($scope)
            : PolymorphicSettings::global();
    }
}
