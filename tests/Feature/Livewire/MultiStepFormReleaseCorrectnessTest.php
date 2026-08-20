<?php

use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;
use Livewire\Livewire;
use Tests\Fixtures\DynamicServerValidationMultiStepForm;

function releaseCorrectnessFields(array $overrides = []): array
{
    return [
        'name' => array_merge([
            'default' => 'Default name',
            'rules' => 'required|string',
            'label' => 'Name',
            'step' => 1,
            'type' => 'text',
        ], $overrides),
    ];
}

test('input steps route form submission through next step instead of final submission', function () {
    Livewire::test(MultiStepForm::class, ['fields' => releaseCorrectnessFields()])
        ->assertSeeHtml('wire:submit="nextStep"')
        ->set('formData.name', 'Jordy')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertSeeHtml('wire:submit="submit"');
});

test('final submission cannot be invoked before the review step', function () {
    Livewire::test(MultiStepForm::class, ['fields' => releaseCorrectnessFields()])
        ->set('formData.name', 'Jordy')
        ->call('submit')
        ->assertSet('step', 1)
        ->assertSet('formData.name', 'Jordy')
        ->assertNotDispatched('multistep-form-submitted');
});

test('server validation rules can inspect configured defaults during mount', function () {
    Livewire::test(DynamicServerValidationMultiStepForm::class, [
        'fields' => releaseCorrectnessFields(['rules' => []]),
    ])->assertSet('observedDefault', 'Default name');
});

test('review revalidation returns to the first invalid field step', function () {
    Livewire::test(DynamicServerValidationMultiStepForm::class, [
        'fields' => releaseCorrectnessFields(['rules' => []]),
    ])
        ->set('formData.name', 'Jordy')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->set('rejectName', true)
        ->call('submit')
        ->assertSet('step', 1)
        ->assertHasErrors(['formData.name' => 'in'])
        ->assertDispatched('multistep-focus-field', field: 'name')
        ->assertNotDispatched('multistep-form-submitted');
});

test('regex rules containing pipes remain supported when supplied as an array', function () {
    Livewire::test(MultiStepForm::class, [
        'fields' => releaseCorrectnessFields([
            'default' => '',
            'rules' => ['required', 'regex:/^(foo|bar)$/'],
        ]),
    ])
        ->set('formData.name', 'bar')
        ->call('nextStep')
        ->assertSet('step', 2);
});
