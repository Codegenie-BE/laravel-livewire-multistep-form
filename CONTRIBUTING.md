# Contributing

Contributions should keep this package small, configuration-driven, Laravel-native, and compatible with the supported Laravel and Livewire versions.

## Development setup

Install the development dependencies:

```bash
composer update
```

Run the local quality gates before opening a pull request:

```bash
composer test
composer format
composer analyse
composer audit
```

GitHub Actions additionally checks recent and minimum supported dependency combinations.

## Pull requests

Keep each pull request focused on one coherent concern. Avoid unrelated refactors, speculative abstractions, or new dependencies unless they are required by the feature itself.

A pull request should explain:

- the concrete problem being solved;
- the intended package behavior;
- compatibility or security implications;
- the tests that demonstrate the change.

## Package design rules

Prefer Laravel and Livewire conventions over custom architecture. Do not add repositories, DTO layers, service layers, use-case classes, or frontend dependencies solely for structure.

The base package must not own application-specific persistence, mail delivery, redirects, authorization, or business workflows.

## Adding or changing field types

A field type is not complete until it has all of the following:

- explicit configuration validation;
- deterministic Blade rendering;
- server-side validation behavior;
- safe default handling;
- review-step behavior;
- accessibility semantics;
- regression tests across the supported Livewire versions.

Unsupported behavior should be rejected rather than silently rendered with undefined semantics.

## Security and accessibility

Treat Livewire public input as untrusted. Keep configuration/state that should not be client-editable locked. Escape user-controlled review output and preserve the full-form validation on submission.

When changing the default view, preserve labels, error relationships, progress semantics, keyboard focus visibility, reduced-motion handling, translated interface copy, and instance-scoped DOM IDs.

For undisclosed vulnerabilities, follow [SECURITY.md](SECURITY.md) instead of opening a public issue.
