<?php

namespace Tests\Fixtures;

use Codegenie\LivewireMultistepForm\Livewire\MultiStepForm;

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
