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

function selectFields(array $overrides = []): array
{
    return [
        'topic' => array_merge([
            'default' => '',
            'rules' => 'required|string',
            'label' => 'Topic',
            'step' => 1,
            'type' => 'select',
            'placeholder' => 'Choose a topic',
            'options' => [
                'general' => 'General question',
                'support' => 'Technical support',
            ],
        ], $overrides),
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

test('submission event exposes the exact validated configured payload', function () {
    Livewire::test(MultiStepForm::class, [
        'fields' => [
            'name' => [
                'rules' => 'required|string',
                'label' => 'Name',
                'step' => 1,
                'type' => 'text',
            ],
        ],
    ])
        ->set('formData.name', 'Jordy')
        ->set('formData.admin', 'must-not-leak')
        ->call('nextStep')
        ->call('submit')
        ->assertDispatched('multistep-form-submitted', data: ['name' => 'Jordy']);
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

test('select fields render their placeholder and configured options', function () {
    Livewire::test(MultiStepForm::class, ['fields' => selectFields()])
        ->assertSee('Choose a topic')
        ->assertSee('General question')
        ->assertSee('Technical support')
        ->assertSeeHtml('<option value="general">General question</option>')
        ->assertSeeHtml('<option value="support">Technical support</option>');
});

test('select values are constrained to configured options server side', function () {
    Livewire::test(MultiStepForm::class, ['fields' => selectFields()])
        ->set('formData.topic', 'admin')
        ->call('nextStep')
        ->assertHasErrors(['formData.topic' => 'in'])
        ->assertSet('step', 1);
});

test('select review displays the human readable option label', function () {
    Livewire::test(MultiStepForm::class, ['fields' => selectFields()])
        ->set('formData.topic', 'support')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertSee('Technical support')
        ->assertDontSee('>support<', false);
});

test('select defaults must exist in configured options', function () {
    $component = new MultiStepForm;

    expect(fn () => $component->mount(selectFields([
        'default' => 'unknown',
    ])))->toThrow(InvalidArgumentException::class, 'default value that is not present');
});

test('select defaults reject boolean and floating point values', function () {
    expect(fn () => (new MultiStepForm)->mount(selectFields([
        'default' => true,
        'options' => [1 => 'Yes'],
    ])))->toThrow(InvalidArgumentException::class, 'string, integer or null');

    expect(fn () => (new MultiStepForm)->mount(selectFields([
        'default' => 1.5,
        'options' => ['1.5' => 'One and a half'],
    ])))->toThrow(InvalidArgumentException::class, 'string, integer or null');
});

test('numeric select option values are normalized for browser submissions', function () {
    $fields = selectFields([
        'default' => 2,
        'options' => [
            1 => 'First',
            2 => 'Second',
        ],
    ]);

    Livewire::test(MultiStepForm::class, ['fields' => $fields])
        ->assertSet('formData.topic', '2')
        ->set('formData.topic', '1')
        ->call('nextStep')
        ->assertSee('First');
});
