<?php

namespace Codegenie\LivewireMultistepForm;

use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LivewireMultistepFormServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $root = dirname(__DIR__);

        $this->loadViewsFrom(
            $root.'/resources/views',
            'livewire-multistep-form'
        );

        $this->loadTranslationsFrom(
            $root.'/resources/lang',
            'livewire-multistep-form'
        );

        $this->publishes([
            $root.'/resources/views' => resource_path('views/vendor/livewire-multistep-form'),
        ], 'livewire-multistep-form-views');

        $this->publishes([
            $root.'/resources/lang' => $this->app->langPath('vendor/livewire-multistep-form'),
        ], 'livewire-multistep-form-translations');

        Livewire::component('codegenie-multistep-form', MultiStepForm::class);
    }
}
