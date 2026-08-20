# Changelog

All notable changes to this package will be documented in this file.

The project follows Semantic Versioning once tagged stable releases begin.

## [Unreleased]

### Added

- Reusable Composer library structure with Laravel package discovery.
- Dynamic consecutive multi-step navigation and review step.
- Per-step and full-form server-side validation.
- Server-only validation rule extension hook for Laravel rule objects, closures, and application-sensitive validation configuration.
- Locked Livewire state for field configuration, current step, and visual color settings.
- Server-side allow-list validation for configured select options.
- Select placeholders, normalized option values, validated defaults, and human-readable review labels.
- English, Dutch, and French package translations.
- Publish tags for package views and translations.
- Instance-scoped DOM IDs for multiple wizard instances on the same page.
- Accessible progress, validation, form, button, and review semantics.
- Pest regression coverage for configuration, security, submission event payloads, select behavior, multi-instance markup, translation parity, and accessibility.
- Latest and minimum Laravel 12/13 + Livewire 3/4 compatibility matrices, including PHP 8.4 and Livewire 3 upper-bound PHP 8.5 coverage.
- Larastan level 8, Pint, Composer validation, Composer audit, Dependabot, and an aggregate `Required checks` CI status.

### Fixed

- Hardcoded wizard behavior that previously assumed exactly three input steps plus a review step.
- Client-mutable field configuration and wizard step state.
- Review rendering that could expose unsafe array content.
- Submission behavior that previously discarded data behind an application-specific redirect.
- Duplicate review labels overwriting values.
- Explicit `null` defaults being converted to empty strings.
- Select values being accepted when they were not part of the configured options.
- Select allow-list validation running as a second validator instead of the main field validation pass.
- Select review output showing internal option keys instead of labels.
- Boolean and floating-point select defaults being ambiguously coerced to browser string values.
- Duplicate DOM IDs when multiple wizard instances are rendered on the same page.
- Production package structure containing a full Laravel application skeleton and unused frontend build files.

## Release process

A versioned section will be created from `Unreleased` when the first tagged stable release is prepared. Do not infer a package version from this file until a matching Git tag exists.
