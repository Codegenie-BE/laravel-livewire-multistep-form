# Laravel Livewire Multi-Step Form

[![Tests](https://github.com/Codegenie-BE/laravel-livewire-multistep-form/actions/workflows/tests.yml/badge.svg)](https://github.com/Codegenie-BE/laravel-livewire-multistep-form/actions/workflows/tests.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2%20to%208.5-777BB4?logo=php)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12%20%7C%2013-FF2D20?logo=laravel)](https://laravel.com/)
[![Livewire](https://img.shields.io/badge/Livewire-3%20%7C%204-FB70A9)](https://livewire.laravel.com/)
[![License](https://img.shields.io/github/license/Codegenie-BE/laravel-livewire-multistep-form)](LICENSE)

A small, configuration-driven multi-step form component for Laravel Livewire. It provides step navigation, per-step validation, a review step, accessible markup, localized interface copy, and a validated submission hook without taking ownership of your application's persistence layer.

## Requirements

The package declares support for:

- PHP 8.2 through supported PHP 8.x releases;
- Laravel 12 or 13;
- Livewire 3.6+ or 4.x.

GitHub Actions verifies both recent and minimum resolvable dependency sets. The matrix covers Laravel 12/13, Livewire 3/4, PHP 8.2/8.3/8.4/8.5 where applicable, plus dedicated `--prefer-lowest --prefer-stable` jobs for every supported Laravel/Livewire major combination.

## Installation

The Composer package name is:

```text
codegenie-be/laravel-livewire-multistep-form
```

Until the first tagged Packagist release is published, install the repository as a Composer VCS package:

```bash
composer config repositories.codegenie-livewire-multistep-form vcs https://github.com/Codegenie-BE/laravel-livewire-multistep-form
composer require codegenie-be/laravel-livewire-multistep-form:dev-main
```

Laravel package discovery registers the service provider automatically.

## Basic usage

Define the fields in your application and pass them to the registered Livewire component:

```blade
<livewire:codegenie-multistep-form
    :fields="[
        'name' => [
            'default' => '',
            'rules' => 'required|string|min:2|max:120',
            'label' => 'Name',
            'step' => 1,
            'type' => 'text',
            'placeholder' => 'Your name',
        ],
        'email' => [
            'default' => '',
            'rules' => 'required|email|max:255',
            'label' => 'Email address',
            'step' => 2,
            'type' => 'email',
        ],
        'topic' => [
            'default' => '',
            'rules' => 'required|string',
            'label' => 'Topic',
            'step' => 2,
            'type' => 'select',
            'placeholder' => 'Choose a topic',
            'options' => [
                'general' => 'General question',
                'support' => 'Support',
            ],
        ],
        'message' => [
            'default' => '',
            'rules' => 'required|string|min:10|max:5000',
            'label' => 'Message',
            'step' => 3,
            'type' => 'textarea',
            'placeholder' => 'How can we help?',
        ],
    ]"
/>
```

Steps must start at `1` and remain consecutive. Multiple fields may share the same step.

## Field configuration

Each field supports these keys:

| Key | Required | Description |
| --- | --- | --- |
| `type` | yes | One of `text`, `email`, `number`, `tel`, `url`, `date`, `textarea`, or `select` |
| `rules` | yes | Laravel validation rules as a non-empty string or an array of non-empty strings |
| `step` | yes | Positive integer step number |
| `label` | no | Human-readable label; generated from the field name when omitted |
| `default` | no | Scalar or `null` initial value; defaults to an empty string |
| `placeholder` | no | Non-empty placeholder text for text-like fields, textarea, or select |
| `options` | for `select` | Non-empty value-to-label map; option values form the server-side allow-list |

Unsupported field types are rejected instead of being rendered with undefined behavior. File uploads, checkboxes, radio groups, repeaters, and nested field names are intentionally outside the current scope.

### Select fields

Select option keys are normalized to strings because browser and Livewire form values are string-based. The package validates submitted select values against the configured option keys on the server, so consumers do not need to duplicate the option list in an `in:` rule.

A non-empty select default must exist in `options`. The empty value is reserved for the unselected / placeholder state. On the review step, the human-readable option label is displayed instead of the raw option key.

## Validation

`nextStep()` validates only the fields on the current step. `submit()` validates the complete form again, so directly invoking the submission action cannot bypass previous steps.

Select values receive an additional server-side allow-list check against their configured options. Validation errors use the same `formData.*` keys as the Livewire bindings and are rendered with accessible error relationships.

## Handling submissions

The base package deliberately does **not** write to a database, send mail, or redirect to an application-specific route. A reusable UI package should not decide how your application stores submitted data.

On a valid submission it:

1. validates the complete form;
2. validates configured select values against their option allow-lists;
3. calls the protected `handleSubmission(array $data)` extension point;
4. dispatches the `multistep-form-submitted` Livewire event with the validated configured data;
5. resets the wizard to its configured defaults.

For server-side persistence, extend the component in your application:

```php
<?php

namespace App\Livewire;

use App\Models\ContactRequest;
use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;

class ContactWizard extends MultiStepForm
{
    protected function handleSubmission(array $data): void
    {
        ContactRequest::query()->create($data);
    }
}
```

Register and render that application component using normal Livewire conventions. Keep authorization, persistence, mail delivery, rate limiting, and other application-specific concerns in your application.

## Localization

The default interface ships with English, Dutch, and French translations. It follows the consuming Laravel application's active locale.

Consumers may publish the translations and override individual strings:

```bash
php artisan vendor:publish --tag=livewire-multistep-form-translations
```

Published translations are placed under Laravel's normal vendor translation path.

## Customizing the view

The default Blade view can be published when an application needs markup or styling changes:

```bash
php artisan vendor:publish --tag=livewire-multistep-form-views
```

The published view is placed under:

```text
resources/views/vendor/livewire-multistep-form/
```

When customizing it, preserve the validation bindings, escaped review output, instance-scoped DOM IDs, and accessibility relationships.

## Tailwind CSS

The package ships Blade markup with Tailwind utility classes but does not install or compile frontend assets for the consuming application.

### Tailwind CSS 4

Add the package views as a source in your application's CSS entrypoint. Adjust the relative path when your CSS file lives elsewhere:

```css
@source '../../vendor/codegenie-be/laravel-livewire-multistep-form/resources/views/**/*.blade.php';
```

### Tailwind CSS 3

Add the package views to the `content` array in `tailwind.config.js`:

```js
content: [
    './resources/**/*.blade.php',
    './vendor/codegenie-be/laravel-livewire-multistep-form/resources/views/**/*.blade.php',
],
```

You may pass six-digit hexadecimal colors for the progress indicator and primary action:

```blade
<livewire:codegenie-multistep-form
    :fields="$fields"
    primary-color="#216ef2"
    button-color="#216ef2"
/>
```

The package validates the color format. The consuming application remains responsible for choosing colors with sufficient contrast.

## Security characteristics

- Field configuration, current step, and color configuration are locked Livewire state.
- User-controlled form values remain mutable and are always validated server-side.
- Submission validates the complete form again.
- Select values are checked against their configured server-side option allow-lists.
- Only configured and validated fields are passed to the submission hook.
- Review values are escaped before rendering.
- Inline color values accept only six-digit hexadecimal values.
- The package does not store data, manage credentials, run queues, require Redis, or call external services.

The application's own authorization, persistence, rate limiting, privacy policy, and data-retention rules remain the responsibility of the consuming project.

See [SECURITY.md](SECURITY.md) for vulnerability reporting guidance.

## Accessibility

The default view includes:

- semantic form, fieldset, label, and definition-list markup;
- a progressbar with ARIA value metadata;
- instance-scoped DOM IDs so multiple wizards can coexist on one page;
- `aria-invalid` and `aria-describedby` on invalid controls;
- alert semantics for validation errors;
- explicit button types;
- visible keyboard focus styles;
- reduced-motion-aware transitions and loading indicators;
- localized screen-reader and interface copy.

Consumers who publish their own view should preserve equivalent semantics.

## Development

Install dependencies:

```bash
composer update
```

Run the quality gates:

```bash
composer test
composer format
composer analyse
composer audit
```

The repository CI additionally runs recent and minimum compatibility matrices. Larastan currently runs at level 8, and the aggregate `Required checks` job only passes when compatibility and quality jobs all succeed.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). Keep changes focused and Laravel-native. New field types must include validation, rendering behavior, accessibility handling, and regression tests for their supported states.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for notable changes prepared for the first stable release.

## License

MIT. See [LICENSE](LICENSE).

Built and maintained by [Codegenie](https://www.codegenie.be/).
