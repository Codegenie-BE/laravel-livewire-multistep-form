<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class MultiStepForm extends Component
{
    public int $step = 1;
    public array $formData = [];

    public string $primaryColor = '#2563eb';
    public string $buttonColor = '#2563eb';

    public array $fields = [];

    public function mount(): void
    {
        if (empty($this->fields)) {
            abort(500, 'No fields defined for the form.');
        }

        $this->formData = collect($this->fields)
            ->mapWithKeys(fn ($data, $field) => [$field => $data['default']])
            ->toArray();
    }

    protected function totalSteps(): int
    {
        return collect($this->fields)->pluck('step')->max();
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

    public function submit(): \Illuminate\Http\RedirectResponse
    {
        $this->validateAll();

        session()->flash('success', 'Form submitted successfully!');
        $this->reset(['formData']);
        $this->step = 1;

        return redirect()->route('thankyou');
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
            ->filter(fn ($data) => $data['step'] === $this->step)
            ->mapWithKeys(fn ($data, $field) => [$field => $data['rules']])
            ->toArray();
    }

    protected function allRules(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn ($data, $field) => [$field => $data['rules']])
            ->toArray();
    }

    protected function attributeLabels(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn ($data, $field) => [$field => $data['label'] ?? ucfirst($field)])
            ->toArray();
    }

    public function getFormData(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn ($data, $field) => [
                $data['label'] ?? ucfirst($field) => $this->formData[$field] ?? ''
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.multi-step-form');
    }
}
