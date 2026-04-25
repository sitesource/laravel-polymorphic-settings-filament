<?php

use Illuminate\Support\Facades\DB;
use SiteSource\PolymorphicSettings\Facades\PolymorphicSettings;
use SiteSource\PolymorphicSettings\Filament\Tests\Support\FakeSettingsHost;
use SiteSource\PolymorphicSettings\Filament\Tests\Support\TestTeam;

describe('global scope', function () {
    beforeEach(function () {
        $this->host = new FakeSettingsHost;
    });

    it('reads existing settings into a name → value array', function () {
        PolymorphicSettings::global()->putMany([
            'commerce.stripe.enabled' => true,
            'site_title' => 'Acme',
        ]);

        expect($this->host->read(['commerce.stripe.enabled', 'site_title', 'missing']))
            ->toBe([
                'commerce.stripe.enabled' => true,
                'site_title' => 'Acme',
                'missing' => null,
            ]);
    });

    it('writes plain values', function () {
        $this->host->write([
            'commerce.stripe.enabled' => true,
            'site_title' => 'Acme',
        ]);

        expect(PolymorphicSettings::global()->get('commerce.stripe.enabled'))->toBeTrue();
        expect(PolymorphicSettings::global()->get('site_title'))->toBe('Acme');
    });

    it('writes encrypted values for keys in the encryptedKeys list', function () {
        $this->host->write(
            [
                'commerce.stripe.public_key' => 'pk-public',
                'commerce.stripe.secret_key' => 'sk-secret',
            ],
            encryptedKeys: ['commerce.stripe.secret_key'],
        );

        $raw = DB::table('polymorphic_settings')
            ->where('key', 'commerce.stripe.secret_key')
            ->first();

        expect((bool) $raw->encrypted)->toBeTrue();
        expect($raw->value)->not->toContain('sk-secret');

        // Public key should be plain.
        $rawPublic = DB::table('polymorphic_settings')
            ->where('key', 'commerce.stripe.public_key')
            ->first();
        expect((bool) $rawPublic->encrypted)->toBeFalse();
    });

    it('roundtrips encrypted values through the SettingsStore decryption', function () {
        $this->host->write(
            ['commerce.stripe.secret_key' => 'sk-secret'],
            encryptedKeys: ['commerce.stripe.secret_key'],
        );

        expect(PolymorphicSettings::global()->get('commerce.stripe.secret_key'))->toBe('sk-secret');
    });
});

describe('scoped to a model', function () {
    it('reads and writes against the scoped store', function () {
        $team = TestTeam::create(['name' => 'Acme']);
        $host = new FakeSettingsHost($team);

        $host->write(['theme' => 'dark']);

        expect(PolymorphicSettings::for($team)->get('theme'))->toBe('dark');
        expect(PolymorphicSettings::global()->get('theme'))->toBeNull();
    });

    it('isolates reads between different scoped hosts', function () {
        $teamA = TestTeam::create(['name' => 'A']);
        $teamB = TestTeam::create(['name' => 'B']);

        PolymorphicSettings::for($teamA)->put('theme', 'dark');
        PolymorphicSettings::for($teamB)->put('theme', 'light');

        expect((new FakeSettingsHost($teamA))->read(['theme']))->toBe(['theme' => 'dark']);
        expect((new FakeSettingsHost($teamB))->read(['theme']))->toBe(['theme' => 'light']);
    });
});
