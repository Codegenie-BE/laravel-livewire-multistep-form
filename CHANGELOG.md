# Changelog

All notable changes to this package will be documented in this file.

The project follows Semantic Versioning for tagged stable releases.

## [Unreleased]

## [1.0.0] - 2026-08-20

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
- Accessible progress, validation, form, button, review, and keyboard focus semantics.
- Instance-scoped focus management after navigation, resets, successful submissions, and validation failures.
- Pest regression coverage for configuration, security, submission event payloads, select behavior, multi-instance markup, translation parity, accessibility, review-gated submission, dynamic server rules, and regex rules.
- Latest and minimum Laravel 12/13 + Livewire 3/4 compatibility matrices, including Livewire 3 upper-bound PHP 8.5 coverage.
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
- Focus events using a payload key that conflicted with Livewire 4 component internals.
- Native form submission on an input step being able to bypass the review step.
- Direct final submission being callable before the review step.
- Final review revalidation errors remaining on the review screen where their input controls were not rendered.
- Server-only validation rules being evaluated before configured form defaults were initialized.
- Production package structure containing a full Laravel application skeleton and unused frontend build files.

## Release process

Stable versions are derived from Git tags. The repository release workflow reads the version from `VERSION`, creates or reuses the matching `vX.Y.Z` tag and GitHub release, verifies that Packagist's GitHub auto-update integration exposes the exact tag and source commit, and removes non-`main` branches after the release run. Packagist itself is configured once through its normal GitHub integration; no Packagist API tokens are stored in this repository.
