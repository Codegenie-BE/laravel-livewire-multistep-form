<?php

use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;
use Codegenie\LivewireMultistepForm\LivewireMultistepFormServiceProvider;
use Illuminate\Support\ServiceProvider;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

function configurationField(array $overrides = []): array
{
    return [
        'name' => array_merge([
            'default' => '',
            'rules' => 'required|string|min:2',
            'label' => 'Name',
            'step' => 1,
            'type' => 'text',
        ], $overrides),
    ];
}

class RecordingMultiStepForm extends MultiStepForm
{
    public int $handledCount = 0;

    /** @var array<string, mixed> */
    public array $handledData = [];

    protected function handleSubmission(array $data): void
    {
        $this->handledCount++;
        $this->handledData = $data;
    }
}

test('an empty field configuration is rejected', function () {
    expect(fn () => (new MultiStepForm)->mount([]))
        ->toThrow(InvalidArgumentException::class, 'At least one field');
});

test('invalid field names are rejected', function () {
    expect(fn () => (new MultiStepForm)->mount([
        '1invalid' => [
            'rules' => 'required',
            'step' => 1,
            'type' => 'text',
        ],
    ]))->toThrow(InvalidArgumentException::class, 'Field names');
});

test('field configuration must be an array', function () {
    expect(fn () => (new MultiStepForm)->mount(['name' => 'invalid']))
        ->toThrow(InvalidArgumentException::class, 'must be an array');
});

test('step must be a positive integer', function () {
    expect(fn () => (new MultiStepForm)->mount(configurationField(['step' => 0])))
        ->toThrow(InvalidArgumentException::class, 'positive integer step');

    expect(fn () => (new MultiStepForm)->mount(configurationField(['step' => '1'])))
        ->toThrow(InvalidArgumentException::class, 'positive integer step');
});

test('validation rules must be non empty strings', function () {
    expect(fn () => (new MultiStepForm)->mount(configurationField(['rules' => ''])))
        ->toThrow(InvalidArgumentException::class, 'validation rules');

    expect(fn () => (new MultiStepForm)->mount(configurationField(['rules' => []])))
        ->toThrow(InvalidArgumentException::class, 'validation rules');

    expect(fn () => (new MultiStepForm)->mount(configurationField(['rules' => ['required', '']])))
        ->toThrow(InvalidArgumentException::class, 'validation rules');
});

test('labels must be non empty strings', function () {
    expect(fn () => (new MultiStepForm)->mount(configurationField(['label' => '   '])))
        ->toThrow(InvalidArgumentException::class, 'non-empty label');
});

test('defaults must remain scalar or null', function () {
    expect(fn () => (new MultiStepForm)->mount(configurationField(['default' => ['invalid']])))
        ->toThrow(InvalidArgumentException::class, 'scalar or null');
});

test('placeholders must be non empty strings when provided', function () {
    expect(fn () => (new MultiStepForm)->mount(configurationField(['placeholder' => ''])))
        ->toThrow(InvalidArgumentException::class, 'non-empty placeholder');

    expect(fn () => (new MultiStepForm)->mount(configurationField(['placeholder' => 123])))
        ->toThrow(InvalidArgumentException::class, 'non-empty placeholder');
});

test('select options must be a non empty value to label map', function () {
    expect(fn () => (new MultiStepForm)->mount(configurationField([
        'type' => 'select',
        'options' => [],
    ])))->toThrow(InvalidArgumentException::class, 'at least one option');

    expect(fn () => (new MultiStepForm)->mount(configurationField([
        'type' => 'select',
        'options' => ['valid' => ''],
    ])))->toThrow(InvalidArgumentException::class, 'invalid option label');

    expect(fn () => (new MultiStepForm)->mount(configurationField([
        'type' => 'select',
        'options' => ['' => 'Reserved'],
    ])))->toThrow(InvalidArgumentException::class, 'empty option value');
});

test('previous step never moves below the first step', function () {
    Livewire::test(MultiStepForm::class, ['fields' => configurationField()])
        ->call('previousStep')
        ->assertSet('step', 1);
});

test('reset restores explicit null defaults', function () {
    Livewire::test(MultiStepForm::class, [
        'fields' => configurationField(['default' => null, 'rules' => 'nullable|string']),
    ])
        ->set('formData.name', 'Changed')
        ->call('resetForm')
        ->assertSet('step', 1)
        ->assertSet('formData.name', null);
});

test('step and visual configuration are locked against client mutation', function () {
    expect(fn () => Livewire::test(MultiStepForm::class, ['fields' => configurationField()])
        ->set('step', 2))
        ->toThrow(CannotUpdateLockedPropertyException::class);

    expect(fn () => Livewire::test(MultiStepForm::class, ['fields' => configurationField()])
        ->set('primaryColor', '#000000'))
        ->toThrow(CannotUpdateLockedPropertyException::class);

    expect(fn () => Livewire::test(MultiStepForm::class, ['fields' => configurationField()])
        ->set('buttonColor', '#000000'))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

test('button color uses the same strict hexadecimal validation', function () {
    expect(fn () => (new MultiStepForm)->mount(
        configurationField(),
        '#2563eb',
        'rgb(0, 0, 0)'
    ))->toThrow(InvalidArgumentException::class, 'six-digit hexadecimal color');
});

test('submission hook receives only validated configured fields once', function () {
    Livewire::test(RecordingMultiStepForm::class, ['fields' => configurationField()])
        ->set('formData.name', 'Jordy')
        ->set('formData.admin', 'should-not-leak')
        ->call('nextStep')
        ->call('submit')
        ->assertSet('handledCount', 1)
        ->assertSet('handledData', ['name' => 'Jordy']);
});

test('the package service provider registers the public component alias', function () {
    Livewire::test('codegenie-multistep-form', ['fields' => configurationField()])
        ->assertSee('Name');
});

test('package views and translations expose dedicated publish tags', function () {
    expect(ServiceProvider::pathsToPublish(
        LivewireMultistepFormServiceProvider::class,
        'livewire-multistep-form-views'
    ))->not->toBeEmpty();

    expect(ServiceProvider::pathsToPublish(
        LivewireMultistepFormServiceProvider::class,
        'livewire-multistep-form-translations'
    ))->not->toBeEmpty();
});
