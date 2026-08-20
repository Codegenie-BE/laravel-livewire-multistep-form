<?php

namespace Codegenie\LivewireMultistepForm;

use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LivewireMultistepFormServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(
            dirname(__DIR__).'/resources/views',
            'livewire-multistep-form'
        );

        Livewire::component('codegenie-multistep-form', MultiStepForm::class);
    }
}
