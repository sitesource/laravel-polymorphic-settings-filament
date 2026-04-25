# Changelog

All notable changes to `laravel-polymorphic-settings-filament` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2026-04-24

### Added

- **Support Filament 3, 4, and 5 in a single package.** Composer constraint widens to `filament/filament: ^3.0||^4.0||^5.0`. The package detects the installed major at runtime and behaves accordingly — consumers don't need to configure anything.

### Changed

- The `form()` method's parameter type now follows Filament's own conventions for the version the host has installed. v3 stays on `Filament\Forms\Form` with `->schema(...)`; v4 and v5 use `Filament\Schemas\Schema` with `->components(...)`. The base class no longer declares `form()`; subclasses provide the version-appropriate signature, which Filament's existing form discovery picks up.
- `FormIntrospector` (internal) is now duck-typed — it accepts whichever container Filament's `getForms()` resolves to. `Field` and `TextInput` sit at the same FQN across all three majors, so the field-name and password-detection logic is shared.
- Test infrastructure: `FormOwner` conditionally extends a v3 vs v4+ base depending on which Filament contracts are loaded; a `FilamentVersion` helper builds the correct schema container in tests, using string class names for the version-specific symbols.
- CI matrix gains a Filament dimension — every PHP/Laravel/OS combination runs the suite against `^3.0`, `^4.0`, and `^5.0`.

### Migration

If you were on the v0.1.x line (Filament 3 only), nothing in your subclass needs to change — same `form(Form $form): Form` signature still works.

If you upgrade your host to Filament 4 or 5, update your `form()` method to take `Schema` and call `->components()` instead of `->schema()`. That's a Filament-side migration, not a polymorphic-settings-filament one — Filament's own upgrade guide has the details.

## [0.1.0] - 2026-04-24

Initial public release.

### Added

- `PolymorphicSettingsPage` base class extending Filament's `Page`. Subclasses define a `form()` schema; mount loads each field's current value from the settings store, save persists them back. Override `scopeFor()` to bind the page to a specific model.
- `BindsToSettings` trait for hosts that want to keep their own `Page` base. Provides `fillFromSettings(array $keys)` and `persistToSettings(array $values, array $encryptedKeys = [])`.
- Auto-encryption: any `TextInput` marked `->password()` is persisted with `encrypted: true`. Roundtrips transparently on next mount.
- `onSaved()` hook for customising the post-save UX (default: Filament success notification).
- Filament 3.x support. Tested against PHP 8.3 / 8.4 / 8.5 and Laravel 12 / 13.

[Unreleased]: https://github.com/sitesource/laravel-polymorphic-settings-filament/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/sitesource/laravel-polymorphic-settings-filament/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/sitesource/laravel-polymorphic-settings-filament/releases/tag/v0.1.0
