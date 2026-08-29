<?php

use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;
use Livewire\Livewire;

test('an optional select accepts its empty placeholder value', function () {
    $fields = [
        'topic' => [
            'default' => '',
            'rules' => 'nullable|string',
            'label' => 'Topic',
            'step' => 1,
            'type' => 'select',
            'placeholder' => 'Choose a topic',
            'options' => [
                'general' => 'General question',
                'support' => 'Technical support',
            ],
        ],
    ];

    Livewire::test(MultiStepForm::class, ['fields' => $fields])
        ->assertSet('formData.topic', '')
        ->call('nextStep')
        ->assertHasNoErrors('formData.topic')
        ->assertSet('step', 2)
        ->call('submit')
        ->assertHasNoErrors('formData.topic')
        ->assertDispatched('multistep-form-submitted')
        ->assertSet('step', 1);
});
