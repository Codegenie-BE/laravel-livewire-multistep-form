<?php

namespace Tests;

use Codegenie\LivewireMultistepForm\LivewireMultistepFormServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            LivewireMultistepFormServiceProvider::class,
        ];
    }
}
