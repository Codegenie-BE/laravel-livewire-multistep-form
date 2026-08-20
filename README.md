# Laravel Livewire Multi-Step Form

[![Tests](https://github.com/Codegenie-BE/laravel-livewire-multistep-form/actions/workflows/tests.yml/badge.svg)](https://github.com/Codegenie-BE/laravel-livewire-multistep-form/actions/workflows/tests.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2%20to%208.5-777BB4?logo=php)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12%20%7C%2013-FF2D20?logo=laravel)](https://laravel.com/)
[![Livewire](https://img.shields.io/badge/Livewire-3%20%7C%204-FB70A9)](https://livewire.laravel.com/)
[![License](https://img.shields.io/github/license/Codegenie-BE/laravel-livewire-multistep-form)](LICENSE)

A small, configuration-driven multi-step form component for Laravel Livewire. It handles step navigation, per-step validation, a review step, accessible form markup, and a validated submission hook without taking ownership of your application's persistence layer.

## Requirements

The package declares support for:

- PHP 8.2 and newer supported PHP 8.x releases
- Laravel 12 or 13
- Livewire 3.6+ or 4.x

The GitHub Actions compatibility matrix tests Laravel 12 and 13 against Livewire 3 and 4 on representative PHP 8.2, 8.3, and 8.5 environments.

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
        ],
        'email' => [
            'default' => '',
            'rules' => 'required|email|max:255',
            'label' => 'Email address',
            'step' => 2,
            'type' => 'email',
        ],
        'topic' => [
            'default' => 'general',
            'rules' => 'required|in:general,support',
            'label' => 'Topic',
            'step' => 2,
            'type' => 'select',
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
| `options` | for `select` | Non-empty value-to-label array |

Unsupported field types are rejected instead of being rendered with undefined behavior. File uploads, checkboxes, radio groups, repeaters, and nested field names are intentionally outside the current scope.

## Validation

`nextStep()` validates only the fields on the current step. `submit()` validates the complete form again, so directly invoking the submission action cannot bypass fields from previous steps.

Validation errors use the same `formData.*` keys as the Livewire bindings and are rendered with accessible error relationships.

## Handling submissions

The base package deliberately does **not** write to a database, send mail, or redirect to an application-specific route. A reusable UI package should not decide how your application stores submitted data.

On a valid submission it:

1. validates the complete form;
2. calls the protected `handleSubmission(array $data)` extension point;
3. dispatches the `multistep-form-submitted` Livewire event with the validated data;
4. resets the wizard to its configured defaults.

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

Register and render that application component using the normal Livewire conventions. Keep authorization, persistence, mail delivery, rate limiting, and other application-specific concerns in your application.

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
- Submission validates the entire form again.
- Review values are escaped before rendering.
- Inline color values accept only six-digit hexadecimal values.
- The package does not store data, manage credentials, run queues, require Redis, or call external services.

The application's own validation rules, authorization, persistence, rate limiting, privacy policy, and data-retention rules remain the responsibility of the consuming project.

## Accessibility

The default view includes:

- semantic form, fieldset, label, and definition-list markup;
- a progressbar with ARIA value metadata;
- `aria-invalid` and `aria-describedby` on invalid controls;
- alert semantics for validation errors;
- explicit button types;
- visible keyboard focus styles;
- reduced-motion-aware transitions and loading indicators.

Consumers who customize or publish their own view should preserve equivalent semantics.

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

The repository CI additionally exercises the supported Laravel, Livewire, and PHP compatibility matrix.

## Contributing

Keep changes focused and Laravel-native. New field types should include validation, rendering behavior, accessibility handling, and tests for their supported states before being added.

Issues and pull requests are welcome through GitHub.

## License

MIT. See [LICENSE](LICENSE).

Built and maintained by [Codegenie](https://www.codegenie.be/).
