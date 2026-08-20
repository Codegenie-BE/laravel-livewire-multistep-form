<?php

namespace Codegenie\LivewireMultistepForm\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class MultiStepForm extends Component
{
    public int $step = 1;

    public array $formData = [];

    public string $primaryColor = '#2563eb';

    public string $buttonColor = '#2563eb';

    public array $fields = [];

    public function mount(
        array $fields = [],
        string $primaryColor = '#2563eb',
        string $buttonColor = '#2563eb'
    ): void {
        $this->fields = $fields;
        $this->primaryColor = $primaryColor;
        $this->buttonColor = $buttonColor;

        if ($this->fields === []) {
            throw new \InvalidArgumentException('At least one field must be defined for the multi-step form.');
        }

        $this->formData = collect($this->fields)
            ->mapWithKeys(fn (array $data, string $field): array => [
                $field => $data['default'] ?? '',
            ])
            ->all();
    }

    protected function totalSteps(): int
    {
        return ((int) collect($this->fields)->pluck('step')->max()) + 1;
    }

    public function nextStep(): void
    {
        $this->validateStep();

        if ($this->step < $this->totalSteps()) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function submit(): void
    {
        $this->validateAll();

        $data = $this->formData;

        $this->dispatch('multistep-form-submitted', data: $data);

        $this->reset('formData');
        $this->step = 1;
    }

    protected function validateFields(array $rules): void
    {
        Validator::make(
            $this->formData,
            $rules,
            [],
            $this->attributeLabels()
        )->validate();
    }

    protected function validateStep(): void
    {
        $this->validateFields($this->rulesForCurrentStep());
    }

    protected function validateAll(): void
    {
        $this->validateFields($this->allRules());
    }

    protected function rulesForCurrentStep(): array
    {
        return collect($this->fields)
            ->filter(fn (array $data): bool => $data['step'] === $this->step)
            ->mapWithKeys(fn (array $data, string $field): array => [
                $field => $data['rules'],
            ])
            ->all();
    }

    protected function allRules(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn (array $data, string $field): array => [
                $field => $data['rules'],
            ])
            ->all();
    }

    protected function attributeLabels(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn (array $data, string $field): array => [
                $field => $data['label'] ?? ucfirst($field),
            ])
            ->all();
    }

    public function getFormData(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn (array $data, string $field): array => [
                $data['label'] ?? ucfirst($field) => $this->formData[$field] ?? '',
            ])
            ->all();
    }

    public function render(): View
    {
        return view('livewire-multistep-form::livewire.multi-step-form');
    }
}
