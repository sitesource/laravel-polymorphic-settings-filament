# Changelog

All notable changes to `laravel-polymorphic-settings-filament` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-04-24

Initial public release.

### Added

- `PolymorphicSettingsPage` base class extending Filament's `Page`. Subclasses define a `form()` schema; mount loads each field's current value from the settings store, save persists them back. Override `scopeFor()` to bind the page to a specific model.
- `BindsToSettings` trait for hosts that want to keep their own `Page` base. Provides `fillFromSettings(array $keys)` and `persistToSettings(array $values, array $encryptedKeys = [])`.
- Auto-encryption: any `TextInput` marked `->password()` is persisted with `encrypted: true`. Roundtrips transparently on next mount.
- `onSaved()` hook for customising the post-save UX (default: Filament success notification).
- Filament 3.x support. Tested against PHP 8.3 / 8.4 / 8.5 and Laravel 12 / 13.

[Unreleased]: https://github.com/sitesource/laravel-polymorphic-settings-filament/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/sitesource/laravel-polymorphic-settings-filament/releases/tag/v0.1.0
