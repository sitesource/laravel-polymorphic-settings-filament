<?php

namespace SiteSource\PolymorphicSettings\Filament\Tests\Support;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

/**
 * Minimal Livewire component used as a Form owner in introspector tests.
 * Filament's Form::make() requires a HasForms instance; this is the
 * lightest possible host.
 */
class FormOwner extends Component implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public array $data = [];

    public function render(): string
    {
        return '';
    }
}
