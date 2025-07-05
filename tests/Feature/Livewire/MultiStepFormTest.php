<?php

use Livewire\Livewire;
use App\Livewire\MultiStepForm;

test('user can complete all steps and submit the form', function () {
    Livewire::test(MultiStepForm::class)
        ->set('name', 'Jordy')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->set('email', 'jordy@example.com')
        ->call('nextStep')
        ->assertSet('step', 3)
        ->set('message', 'This is a test message.')
        ->call('submit')
        ->assertSessionHas('success')
        ->assertSet('step', 1);
});

test('name is required on step 1', function () {
    Livewire::test(MultiStepForm::class)
        ->set('name', '')
        ->call('nextStep')
        ->assertHasErrors(['name' => 'required']);
});

test('email must be valid on step 2', function () {
    Livewire::test(MultiStepForm::class)
        ->set('name', 'Jordy')
        ->call('nextStep')
        ->set('email', 'invalid-email')
        ->call('nextStep')
        ->assertHasErrors(['email' => 'email']);
});

test('message is required on step 3', function () {
    Livewire::test(MultiStepForm::class)
        ->set('name', 'Jordy')
        ->call('nextStep')
        ->set('email', 'jordy@example.com')
        ->call('nextStep')
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors(['message' => 'required']);
});
