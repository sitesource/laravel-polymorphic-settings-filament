<?php

namespace SiteSource\PolymorphicSettings\Filament\Tests;

use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use SiteSource\PolymorphicSettings\Filament\FilamentSettingsServiceProvider;
use SiteSource\PolymorphicSettings\PolymorphicSettingsServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            FormsServiceProvider::class,
            NotificationsServiceProvider::class,
            FilamentServiceProvider::class,
            PolymorphicSettingsServiceProvider::class,
            FilamentSettingsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('app.key', 'base64:AckfSECXIvnK5r28GVIWUAxmbBSjTsmFqWdR1ymp2ck=');
        config()->set('database.default', 'testing');
        config()->set('cache.default', 'array');

        // Run the polymorphic-settings core migration directly out of
        // the vendor directory.
        $migrationDir = __DIR__.'/../vendor/sitesource/laravel-polymorphic-settings/database/migrations';
        foreach (File::allFiles($migrationDir) as $migration) {
            (include $migration->getRealPath())->up();
        }

        // Test parent model used to exercise scoped settings.
        Schema::create('test_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
}
