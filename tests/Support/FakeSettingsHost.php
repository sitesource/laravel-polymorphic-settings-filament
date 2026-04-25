<?php

namespace SiteSource\PolymorphicSettings\Filament\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use SiteSource\PolymorphicSettings\Filament\Concerns\BindsToSettings;

/**
 * Plain class that exercises BindsToSettings without requiring a
 * Filament/Livewire host. Tests poke its public wrappers.
 */
class FakeSettingsHost
{
    use BindsToSettings;

    public function __construct(private ?Model $scope = null) {}

    protected function settingsScope(): ?Model
    {
        return $this->scope;
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    public function read(array $keys): array
    {
        return $this->fillFromSettings($keys);
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<int, string>  $encryptedKeys
     */
    public function write(array $values, array $encryptedKeys = []): void
    {
        $this->persistToSettings($values, $encryptedKeys);
    }
}
