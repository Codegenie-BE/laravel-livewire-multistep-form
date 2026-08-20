<?php

use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;
use Livewire\Livewire;

function accessibilityFields(): array
{
    return [
        'name' => [
            'rules' => 'required|min:2',
            'label' => 'Name',
            'step' => 1,
            'type' => 'text',
        ],
    ];
}

test('the wizard renders semantic form and progress markup', function () {
    Livewire::test(MultiStepForm::class, ['fields' => accessibilityFields()])
        ->assertSeeHtml('<form')
        ->assertSeeHtml('wire:submit="submit"')
        ->assertSeeHtml('role="progressbar"')
        ->assertSeeHtml('aria-valuemin="1"')
        ->assertSeeHtml('aria-valuemax="2"')
        ->assertSeeHtml('type="button"')
        ->assertSeeHtml('wire:model="formData.name"');
});

test('validation errors are announced as alerts', function () {
    Livewire::test(MultiStepForm::class, ['fields' => accessibilityFields()])
        ->set('formData.name', '')
        ->call('nextStep')
        ->assertSeeHtml('role="alert"')
        ->assertSeeHtml('aria-invalid="true"')
        ->assertSeeHtml('aria-describedby="error-name"');
});

test('the review step uses definition list semantics and a submit button', function () {
    Livewire::test(MultiStepForm::class, ['fields' => accessibilityFields()])
        ->set('formData.name', 'Jordy')
        ->call('nextStep')
        ->assertSeeHtml('<dl')
        ->assertSeeHtml('<dt')
        ->assertSeeHtml('<dd')
        ->assertSeeHtml('type="submit"');
});
