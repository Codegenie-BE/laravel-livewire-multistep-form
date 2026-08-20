<?php

namespace Codegenie\LivewireMultistepForm\Livewire;

use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * @phpstan-type FieldConfig array{
 *     default: bool|float|int|string|null,
 *     rules: string|array<array-key, string>,
 *     label: string,
 *     step: int,
 *     type: string,
 *     options: array<array-key, string>
 * }
 */
class MultiStepForm extends Component
{
    private const SUPPORTED_FIELD_TYPES = [
        'text',
        'email',
        'number',
        'tel',
        'url',
        'date',
        'textarea',
        'select',
    ];

    #[Locked]
    public int $step = 1;

    /** @var array<string, mixed> */
    public array $formData = [];

    #[Locked]
    public string $primaryColor = '#2563eb';

    #[Locked]
    public string $buttonColor = '#2563eb';

    /** @var array<string, FieldConfig> */
    #[Locked]
    public array $fields = [];

    /**
     * @param  array<array-key, mixed>  $fields
     */
    public function mount(
        array $fields = [],
        string $primaryColor = '#2563eb',
        string $buttonColor = '#2563eb'
    ): void {
        $this->fields = $this->validateAndNormalizeFields($fields);
        $this->primaryColor = $this->validateColor($primaryColor, 'primaryColor');
        $this->buttonColor = $this->validateColor($buttonColor, 'buttonColor');
        $this->resetFormData();
    }

    public function nextStep(): void
    {
        if ($this->isReviewStep()) {
            return;
        }

        $this->validateCurrentStep();
        $this->step++;
        $this->resetValidation();
    }

    public function previousStep(): void
    {
        if ($this->step <= 1) {
            return;
        }

        $this->step--;
        $this->resetValidation();
    }

    public function submit(): void
    {
        $validated = $this->validate($this->allRules(), [], $this->attributeLabels());

        /** @var array<string, mixed> $data */
        $data = $validated['formData'] ?? [];

        $this->handleSubmission($data);
        $this->dispatch('multistep-form-submitted', data: $data);
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->step = 1;
        $this->resetFormData();
        $this->resetValidation();
    }

    public function totalInputSteps(): int
    {
        return (int) collect($this->fields)->pluck('step')->max();
    }

    public function reviewStep(): int
    {
        return $this->totalInputSteps() + 1;
    }

    public function totalSteps(): int
    {
        return $this->reviewStep();
    }

    public function isReviewStep(): bool
    {
        return $this->step === $this->reviewStep();
    }

    /**
     * @return array<string, FieldConfig>
     */
    public function currentStepFields(): array
    {
        return collect($this->fields)
            ->filter(fn (array $config): bool => $config['step'] === $this->step)
            ->all();
    }

    /**
     * @return list<array{name: string, label: string, value: mixed}>
     */
    public function reviewItems(): array
    {
        return collect($this->fields)
            ->map(fn (array $config, string $field): array => [
                'name' => $field,
                'label' => $config['label'],
                'value' => $this->formData[$field] ?? '',
            ])
            ->values()
            ->all();
    }

    /**
     * @param  FieldConfig  $config
     */
    public function isFieldRequired(array $config): bool
    {
        $rules = $config['rules'];

        if (is_string($rules)) {
            return in_array('required', explode('|', $rules), true);
        }

        return in_array('required', $rules, true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleSubmission(array $data): void
    {
        // Extension point for consumers that subclass the component.
    }

    protected function validateCurrentStep(): void
    {
        $this->validate($this->rulesForCurrentStep(), [], $this->attributeLabels());
    }

    /**
     * @return array<string, string|array<array-key, string>>
     */
    protected function rulesForCurrentStep(): array
    {
        return collect($this->currentStepFields())
            ->mapWithKeys(fn (array $config, string $field): array => [
                "formData.{$field}" => $config['rules'],
            ])
            ->all();
    }

    /**
     * @return array<string, string|array<array-key, string>>
     */
    protected function allRules(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn (array $config, string $field): array => [
                "formData.{$field}" => $config['rules'],
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function attributeLabels(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn (array $config, string $field): array => [
                "formData.{$field}" => $config['label'],
            ])
            ->all();
    }

    protected function resetFormData(): void
    {
        $this->formData = collect($this->fields)
            ->mapWithKeys(fn (array $config, string $field): array => [
                $field => $config['default'],
            ])
            ->all();
    }

    /**
     * @param  array<array-key, mixed>  $fields
     * @return array<string, FieldConfig>
     */
    protected function validateAndNormalizeFields(array $fields): array
    {
        if ($fields === []) {
            throw new InvalidArgumentException('At least one field must be defined for the multi-step form.');
        }

        /** @var array<string, FieldConfig> $normalized */
        $normalized = [];

        foreach ($fields as $field => $config) {
            if (! is_string($field) || preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $field) !== 1) {
                throw new InvalidArgumentException('Field names may only contain letters, numbers and underscores, and may not start with a number.');
            }

            if (! is_array($config)) {
                throw new InvalidArgumentException("Configuration for field [{$field}] must be an array.");
            }

            $normalized[$field] = $this->normalizeField($field, $config);
        }

        $steps = collect($normalized)
            ->pluck('step')
            ->unique()
            ->sort()
            ->values()
            ->all();

        $expectedSteps = range(1, max($steps));

        if ($steps !== $expectedSteps) {
            throw new InvalidArgumentException('Form steps must be consecutive and start at step 1.');
        }

        return $normalized;
    }

    /**
     * @param  array<array-key, mixed>  $config
     * @return FieldConfig
     */
    protected function normalizeField(string $field, array $config): array
    {
        $step = $config['step'] ?? null;

        if (! is_int($step) || $step < 1) {
            throw new InvalidArgumentException("Field [{$field}] must define a positive integer step.");
        }

        $type = $config['type'] ?? null;

        if (! is_string($type) || ! in_array($type, self::SUPPORTED_FIELD_TYPES, true)) {
            throw new InvalidArgumentException("Field [{$field}] uses an unsupported type.");
        }

        $rules = $config['rules'] ?? null;

        if (! $this->hasValidRules($rules)) {
            throw new InvalidArgumentException("Field [{$field}] must define validation rules as a string or an array of strings.");
        }

        $label = $config['label'] ?? ucfirst(str_replace('_', ' ', $field));

        if (! is_string($label) || trim($label) === '') {
            throw new InvalidArgumentException("Field [{$field}] must have a non-empty label.");
        }

        $default = array_key_exists('default', $config) ? $config['default'] : '';

        if ($default !== null && ! is_scalar($default)) {
            throw new InvalidArgumentException("Field [{$field}] must have a scalar or null default value.");
        }

        $options = $config['options'] ?? [];
        $normalizedOptions = [];

        if ($type === 'select') {
            $this->validateOptions($field, $options);
            $normalizedOptions = $options;
        }

        return [
            'default' => $default,
            'rules' => $rules,
            'label' => trim($label),
            'step' => $step,
            'type' => $type,
            'options' => $normalizedOptions,
        ];
    }

    /**
     * @phpstan-assert-if-true string|array<array-key, string> $rules
     */
    protected function hasValidRules(mixed $rules): bool
    {
        if (is_string($rules)) {
            return trim($rules) !== '';
        }

        if (! is_array($rules) || $rules === []) {
            return false;
        }

        foreach ($rules as $rule) {
            if (! is_string($rule) || trim($rule) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @phpstan-assert array<array-key, string> $options
     */
    protected function validateOptions(string $field, mixed $options): void
    {
        if (! is_array($options) || $options === []) {
            throw new InvalidArgumentException("Select field [{$field}] must define at least one option.");
        }

        foreach ($options as $label) {
            if (! is_string($label) || trim($label) === '') {
                throw new InvalidArgumentException("Select field [{$field}] contains an invalid option label.");
            }
        }
    }

    protected function validateColor(string $color, string $name): string
    {
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color) !== 1) {
            throw new InvalidArgumentException("[{$name}] must be a six-digit hexadecimal color.");
        }

        return strtolower($color);
    }

    public function render(): View
    {
        /** @var view-string $view */
        $view = 'livewire-multistep-form::livewire.multi-step-form';

        return view($view);
    }
}
