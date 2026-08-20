# Security Policy

## Supported versions

Before the first stable tagged release, security fixes are maintained on the current `main` branch. After stable releases begin, this document will list the supported release lines explicitly.

## Reporting a vulnerability

Please do not open a public issue for an undisclosed vulnerability.

Use GitHub's private security reporting facilities when they are available for this repository, or contact the maintainers through the repository organization to coordinate a private disclosure. Include enough information to reproduce and assess the issue, such as the affected package revision, Laravel and Livewire versions, attack prerequisites, expected behavior, observed behavior, and a minimal proof of concept where appropriate.

Do not include real credentials, production data, personal data, or unrelated third-party secrets in a report.

## Package security boundary

This package is responsible for the security properties of its own wizard component, including:

- server-side validation of configured form fields;
- locked Livewire configuration and step state;
- select option allow-list enforcement;
- escaped review output;
- safe handling of configured visual color values;
- limiting the submission payload to validated configured fields.

The consuming Laravel application remains responsible for authorization, authentication, persistence, rate limiting, CSRF/session configuration, mail delivery, privacy notices, retention policies, and any domain-specific validation or access control.

## Disclosure expectations

Maintainers will validate reports against supported code before classifying severity. Fixes should be narrowly scoped, covered by regression tests, and released without unnecessary architectural changes.
