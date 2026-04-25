<?php

namespace SiteSource\PolymorphicSettings\Filament;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentSettingsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        // Nothing to register beyond the package name. The integration is
        // entirely classes the host extends — there are no migrations,
        // config, or commands to publish.
        $package->name('polymorphic-settings-filament');
    }
}
