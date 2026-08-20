<?php

namespace Tests\Fixtures;

use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;
use Illuminate\Validation\Rule;

class ServerValidationMultiStepForm extends MultiStepForm
{
    /**
     * @return array<string, mixed>
     */
    protected function serverValidationRules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                Rule::in(['allowed@example.com']),
            ],
        ];
    }
}
