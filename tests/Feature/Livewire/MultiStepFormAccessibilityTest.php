<?php

use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;
use Illuminate\Support\Facades\Blade;
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
        ->assertSeeHtml('wire:model="formData.name"')
        ->assertSeeHtml('id="multistep-');
});

test('validation errors are announced as alerts with instance scoped relationships', function () {
    Livewire::test(MultiStepForm::class, ['fields' => accessibilityFields()])
        ->set('formData.name', '')
        ->call('nextStep')
        ->assertSeeHtml('role="alert"')
        ->assertSeeHtml('aria-invalid="true"')
        ->assertSeeHtml('aria-describedby="multistep-')
        ->assertSeeHtml('-error-name"');
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

test('package interface copy follows the application locale', function () {
    app()->setLocale('nl');

    Livewire::test(MultiStepForm::class, ['fields' => accessibilityFields()])
        ->assertSee('Stap 1 van 2')
        ->assertSee('Formuliervoortgang')
        ->assertSee('Volgende');
});

test('multiple wizard instances render without duplicate DOM ids', function () {
    $fields = accessibilityFields();

    $html = Blade::render(<<<'BLADE'
        <div>
            <livewire:codegenie-multistep-form :fields="$fields" key="first-wizard" />
            <livewire:codegenie-multistep-form :fields="$fields" key="second-wizard" />
        </div>
    BLADE, compact('fields'));

    preg_match_all('/\sid="([^"]+)"/', $html, $matches);

    $ids = $matches[1];
    $fieldIds = array_values(array_filter(
        $ids,
        fn (string $id): bool => str_ends_with($id, '-field-name')
    ));

    expect($ids)
        ->not->toBeEmpty()
        ->and(array_values(array_unique($ids)))->toHaveCount(count($ids))
        ->and($fieldIds)->toHaveCount(2)
        ->and(array_values(array_unique($fieldIds)))->toHaveCount(2);
});
