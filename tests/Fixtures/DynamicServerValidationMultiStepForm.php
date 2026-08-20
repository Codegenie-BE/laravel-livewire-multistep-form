<?php

namespace Tests\Fixtures;

use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;
use Illuminate\Validation\Rule;

class DynamicServerValidationMultiStepForm extends MultiStepForm
{
    public bool $rejectName = false;

    public ?string $observedDefault = null;

    protected function serverValidationRules(): array
    {
        $this->observedDefault = isset($this->formData['name'])
            ? (string) $this->formData['name']
            : null;

        return [
            'name' => $this->rejectName
                ? [Rule::in(['Allowed'])]
                : ['required', 'string'],
        ];
    }
}
