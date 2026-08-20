<?php

namespace Codegenie\LivewireMultistepForm\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
 *     placeholder: string|null,
 *     options: array<string, string>
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
        $this->assertValidationRulesDefined();
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
        $this->dispatchFocusHeading();
    }

    public function previousStep(): void
    {
        if ($this->step <= 1) {
            return;
        }

        $this->step--;
        $this->resetValidation();
        $this->dispatchFocusHeading();
    }

    public function submit(): void
    {
        $validated = $this->validateFields($this->fields);

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
        $this->dispatchFocusHeading();
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
        $items = [];

        foreach ($this->fields as $field => $config) {
            $items[] = [
                'name' => $field,
                'label' => $config['label'],
                'value' => $this->reviewValue($field, $config),
            ];
        }

        return $items;
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
     * Add Laravel validation rules that must remain server-side.
     *
     * Keys are configured field names without the formData prefix. Values may
     * use any rule shape accepted by Laravel's validator, including rule
     * objects and closures. These rules are rebuilt on every request and are
     * never stored in the public Livewire field configuration.
     *
     * @return array<string, mixed>
     */
    protected function serverValidationRules(): array
    {
        return [];
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
        $this->validateFields($this->currentStepFields());
    }

    /**
     * @param  array<string, FieldConfig>  $fields
     * @return array<string, mixed>
     */
    protected function validateFields(array $fields): array
    {
        try {
            return $this->validate(
                $this->rulesForFields($fields),
                [],
                $this->attributeLabels()
            );
        } catch (ValidationException $exception) {
            $this->dispatchFocusForFirstError($exception);

            throw $exception;
        }
    }

    protected function dispatchFocusHeading(): void
    {
        $this->dispatch(
            'multistep-focus-heading',
            instanceId: $this->getId()
        );
    }

    protected function dispatchFocusForFirstError(ValidationException $exception): void
    {
        $errorKey = array_key_first($exception->errors());

        if (! is_string($errorKey) || ! str_starts_with($errorKey, 'formData.')) {
            return;
        }

        $field = substr($errorKey, strlen('formData.'));

        if (! array_key_exists($field, $this->fields)) {
            return;
        }

        $this->dispatch(
            'multistep-focus-field',
            instanceId: $this->getId(),
            field: $field
        );
    }

    /**
     * @param  array<string, FieldConfig>  $fields
     * @return array<string, array<int, mixed>>
     */
    protected function rulesForFields(array $fields): array
    {
        $serverRules = $this->validatedServerValidationRules();
        $rules = [];

        foreach ($fields as $field => $config) {
            $fieldRules = $this->ruleList($config['rules']);

            if (array_key_exists($field, $serverRules)) {
                $fieldRules = [
                    ...$fieldRules,
                    ...$this->ruleList($serverRules[$field]),
                ];
            }

            if ($config['type'] === 'select') {
                $fieldRules[] = Rule::in(array_keys($config['options']));
            }

            $rules["formData.{$field}"] = $fieldRules;
        }

        return $rules;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function allRules(): array
    {
        return $this->rulesForFields($this->fields);
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

        $steps = [];

        foreach ($normalized as $config) {
            $steps[] = $config['step'];
        }

        $steps = array_values(array_unique($steps));
        sort($steps);

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

        $rules = $config['rules'] ?? [];

        if (! $this->hasValidConfiguredRules($rules)) {
            throw new InvalidArgumentException("Field [{$field}] must define validation rules as a string or an array of strings when provided.");
        }

        $label = $config['label'] ?? ucfirst(str_replace('_', ' ', $field));

        if (! is_string($label) || trim($label) === '') {
            throw new InvalidArgumentException("Field [{$field}] must have a non-empty label.");
        }

        $default = array_key_exists('default', $config) ? $config['default'] : '';

        if ($default !== null && ! is_scalar($default)) {
            throw new InvalidArgumentException("Field [{$field}] must have a scalar or null default value.");
        }

        $placeholder = $config['placeholder'] ?? null;

        if ($placeholder !== null && (! is_string($placeholder) || trim($placeholder) === '')) {
            throw new InvalidArgumentException("Field [{$field}] must have a non-empty placeholder when one is provided.");
        }

        $normalizedOptions = [];

        if ($type === 'select') {
            $normalizedOptions = $this->normalizeOptions($field, $config['options'] ?? []);
            $default = $this->normalizeSelectDefault($field, $default, $normalizedOptions);
        }

        return [
            'default' => $default,
            'rules' => $rules,
            'label' => trim($label),
            'step' => $step,
            'type' => $type,
            'placeholder' => $placeholder === null ? null : trim($placeholder),
            'options' => $normalizedOptions,
        ];
    }

    /**
     * @phpstan-assert-if-true string|array<array-key, string> $rules
     */
    protected function hasValidConfiguredRules(mixed $rules): bool
    {
        if (is_string($rules)) {
            return trim($rules) !== '';
        }

        if (! is_array($rules)) {
            return false;
        }

        foreach ($rules as $rule) {
            if (! is_string($rule) || trim($rule) === '') {
                return false;
            }
        }

        return true;
    }

    protected function assertValidationRulesDefined(): void
    {
        $serverRules = $this->validatedServerValidationRules();

        foreach ($this->fields as $field => $config) {
            if ($this->ruleList($config['rules']) !== []) {
                continue;
            }

            if (array_key_exists($field, $serverRules)) {
                continue;
            }

            throw new InvalidArgumentException("Field [{$field}] must define configured rules or server validation rules.");
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedServerValidationRules(): array
    {
        $rules = $this->serverValidationRules();

        foreach ($rules as $field => $fieldRules) {
            if (! array_key_exists($field, $this->fields)) {
                throw new InvalidArgumentException("Server validation rules reference unknown field [{$field}].");
            }

            if ($this->ruleList($fieldRules) === []) {
                throw new InvalidArgumentException("Server validation rules for field [{$field}] may not be empty.");
            }
        }

        return $rules;
    }

    /**
     * @return array<int, mixed>
     */
    protected function ruleList(mixed $rules): array
    {
        if ($rules === null || $rules === []) {
            return [];
        }

        if (is_string($rules)) {
            return trim($rules) === '' ? [] : explode('|', $rules);
        }

        if (is_array($rules)) {
            return array_values($rules);
        }

        if (is_object($rules)) {
            return [$rules];
        }

        throw new InvalidArgumentException('Server validation rules must use rule shapes supported by Laravel.');
    }

    /**
     * @return array<string, string>
     */
    protected function normalizeOptions(string $field, mixed $options): array
    {
        if (! is_array($options) || $options === []) {
            throw new InvalidArgumentException("Select field [{$field}] must define at least one option.");
        }

        $normalized = [];

        foreach ($options as $value => $label) {
            $value = (string) $value;

            if ($value === '') {
                throw new InvalidArgumentException("Select field [{$field}] may not use an empty option value because it is reserved for the placeholder.");
            }

            if (! is_string($label) || trim($label) === '') {
                throw new InvalidArgumentException("Select field [{$field}] contains an invalid option label.");
            }

            $normalized[$value] = trim($label);
        }

        return $normalized;
    }

    /**
     * @param  array<string, string>  $options
     */
    protected function normalizeSelectDefault(string $field, mixed $default, array $options): ?string
    {
        if ($default === null || $default === '') {
            return $default;
        }

        if (! is_string($default) && ! is_int($default)) {
            throw new InvalidArgumentException("Select field [{$field}] must use a string, integer or null default value.");
        }

        $value = (string) $default;

        if (! array_key_exists($value, $options)) {
            throw new InvalidArgumentException("Select field [{$field}] has a default value that is not present in its options.");
        }

        return $value;
    }

    /**
     * @param  FieldConfig  $config
     */
    protected function reviewValue(string $field, array $config): mixed
    {
        $value = $this->formData[$field] ?? null;

        if ($config['type'] === 'select' && $value !== null && $value !== '') {
            return $config['options'][(string) $value] ?? $value;
        }

        return $value ?? '';
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
