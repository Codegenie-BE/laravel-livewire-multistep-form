<?php

use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;
use Livewire\Livewire;

function packageFields(): array
{
    return [
        'name' => [
            'default' => '',
            'rules' => 'required|min:2',
            'label' => 'Name',
            'step' => 1,
            'type' => 'text',
        ],
        'email' => [
            'default' => '',
            'rules' => 'required|email',
            'label' => 'Email',
            'step' => 2,
            'type' => 'email',
        ],
        'message' => [
            'default' => '',
            'rules' => 'required|min:10',
            'label' => 'Message',
            'step' => 3,
            'type' => 'textarea',
        ],
    ];
}

test('the package component completes the basic wizard flow', function () {
    Livewire::test(MultiStepForm::class, ['fields' => packageFields()])
        ->set('formData.name', 'Jordy')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->set('formData.email', 'jordy@example.com')
        ->call('nextStep')
        ->assertSet('step', 3)
        ->set('formData.message', 'This is a test message.')
        ->call('nextStep')
        ->assertSet('step', 4)
        ->call('submit')
        ->assertDispatched('multistep-form-submitted')
        ->assertSet('step', 1)
        ->assertSet('formData', []);
});

test('the current step is validated before advancing', function () {
    Livewire::test(MultiStepForm::class, ['fields' => packageFields()])
        ->set('formData.name', '')
        ->call('nextStep')
        ->assertHasErrors(['name' => 'required'])
        ->assertSet('step', 1);
});
