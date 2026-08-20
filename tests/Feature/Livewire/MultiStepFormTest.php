<?php

use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
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
        ->assertSet('formData', [
            'name' => '',
            'email' => '',
            'message' => '',
        ]);
});

test('the current step is validated before advancing', function () {
    Livewire::test(MultiStepForm::class, ['fields' => packageFields()])
        ->set('formData.name', '')
        ->call('nextStep')
        ->assertHasErrors(['formData.name' => 'required'])
        ->assertSet('step', 1);
});

test('a one-step form advances to its dynamic review step', function () {
    $fields = [
        'name' => [
            'rules' => 'required',
            'label' => 'Name',
            'step' => 1,
            'type' => 'text',
        ],
    ];

    Livewire::test(MultiStepForm::class, ['fields' => $fields])
        ->set('formData.name', 'Jordy')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertSee('Review your information');
});

test('a four-step form advances to step five for review', function () {
    $fields = [];

    foreach (range(1, 4) as $step) {
        $fields["field_{$step}"] = [
            'rules' => 'required',
            'label' => "Field {$step}",
            'step' => $step,
            'type' => 'text',
        ];
    }

    $component = Livewire::test(MultiStepForm::class, ['fields' => $fields]);

    foreach (range(1, 4) as $step) {
        $component
            ->set("formData.field_{$step}", "value {$step}")
            ->call('nextStep');
    }

    $component
        ->assertSet('step', 5)
        ->assertSee('Review your information');
});

test('submit validates all fields even when called directly', function () {
    Livewire::test(MultiStepForm::class, ['fields' => packageFields()])
        ->call('submit')
        ->assertHasErrors([
            'formData.name' => 'required',
            'formData.email' => 'required',
            'formData.message' => 'required',
        ])
        ->assertNotDispatched('multistep-form-submitted');
});

test('field configuration is locked against client-side mutation', function () {
    expect(fn () => Livewire::test(MultiStepForm::class, ['fields' => packageFields()])
        ->set('fields.name.rules', 'nullable'))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

test('sparse step numbers are rejected', function () {
    $component = new MultiStepForm;

    expect(fn () => $component->mount([
        'first' => [
            'rules' => 'required',
            'step' => 1,
            'type' => 'text',
        ],
        'third' => [
            'rules' => 'required',
            'step' => 3,
            'type' => 'text',
        ],
    ]))->toThrow(InvalidArgumentException::class, 'Form steps must be consecutive');
});

test('unsupported field types are rejected', function () {
    $component = new MultiStepForm;

    expect(fn () => $component->mount([
        'attachment' => [
            'rules' => 'required',
            'step' => 1,
            'type' => 'file',
        ],
    ]))->toThrow(InvalidArgumentException::class, 'unsupported type');
});

test('unsafe color values are rejected', function () {
    $component = new MultiStepForm;

    expect(fn () => $component->mount([
        'name' => [
            'rules' => 'required',
            'step' => 1,
            'type' => 'text',
        ],
    ], 'red; background:url(javascript:alert(1))'))
        ->toThrow(InvalidArgumentException::class, 'six-digit hexadecimal color');
});

test('review preserves fields that share the same label', function () {
    $fields = [
        'first_name' => [
            'rules' => 'required',
            'label' => 'Name',
            'step' => 1,
            'type' => 'text',
        ],
        'nickname' => [
            'rules' => 'required',
            'label' => 'Name',
            'step' => 1,
            'type' => 'text',
        ],
    ];

    Livewire::test(MultiStepForm::class, ['fields' => $fields])
        ->set('formData.first_name', 'Alpha')
        ->set('formData.nickname', 'Beta')
        ->call('nextStep')
        ->assertSee('Alpha')
        ->assertSee('Beta');
});

test('review escapes user supplied html', function () {
    $fields = [
        'name' => [
            'rules' => 'required',
            'label' => 'Name',
            'step' => 1,
            'type' => 'text',
        ],
    ];

    Livewire::test(MultiStepForm::class, ['fields' => $fields])
        ->set('formData.name', '<script>alert(1)</script>')
        ->call('nextStep')
        ->assertSee('<script>alert(1)</script>')
        ->assertDontSeeHtml('<script>alert(1)</script>');
});
