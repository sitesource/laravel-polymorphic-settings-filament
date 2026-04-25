<?php

namespace SiteSource\PolymorphicSettings\Filament\Tests\Support;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Component;

/**
 * Minimal Livewire component used as the schema/form owner in tests.
 * Filament's Form::make() (v3) / Schema::make() (v4+) require a host
 * implementing HasForms / HasSchemas; this stub satisfies whichever
 * is installed.
 *
 * The contract + trait selection happens in this same file so the
 * resulting class is the same FQN regardless of Filament version.
 */
if (interface_exists(HasSchemas::class)) {
    abstract class FormOwnerBase extends Component implements HasSchemas
    {
        use InteractsWithSchemas;
    }
} else {
    abstract class FormOwnerBase extends Component implements HasForms
    {
        use InteractsWithForms;
    }
}

class FormOwner extends FormOwnerBase
{
    /** @var array<string, mixed> */
    public array $data = [];

    public function render(): string
    {
        return '';
    }
}
