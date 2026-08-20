@php
    $stepFields = $this->currentStepFields();
    $totalSteps = $this->totalSteps();
    $isReviewStep = $this->isReviewStep();
    $progress = (int) round(($step / $totalSteps) * 100);
@endphp

<form wire:submit="submit" class="max-w-xl mx-auto bg-white p-6 rounded shadow-md" aria-labelledby="multistep-form-heading">
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <span id="multistep-form-heading" class="text-sm font-semibold text-gray-800" aria-live="polite">
                Step {{ $step }} of {{ $totalSteps }}
            </span>
        </div>

        <div
            class="w-full bg-gray-200 rounded-full h-3 overflow-hidden"
            role="progressbar"
            aria-label="Form progress"
            aria-valuemin="1"
            aria-valuemax="{{ $totalSteps }}"
            aria-valuenow="{{ $step }}"
            aria-valuetext="Step {{ $step }} of {{ $totalSteps }}, {{ $progress }} percent complete"
        >
            <div
                class="h-full rounded-full transition-all duration-300 motion-reduce:transition-none"
                style="width: {{ $progress }}%; background-color: {{ $primaryColor }}"
                aria-hidden="true"
            ></div>
        </div>
    </div>

    @if (! $isReviewStep)
        <fieldset>
            <legend class="sr-only">Fields for step {{ $step }}</legend>

            @foreach ($stepFields as $field => $config)
                @php
                    $inputId = 'field-' . $field;
                    $errorId = 'error-' . $field;
                    $errorKey = 'formData.' . $field;
                    $hasError = $errors->has($errorKey);
                    $isRequired = $this->isFieldRequired($config);
                    $baseClasses = 'w-full border p-2 rounded focus-visible:outline-2 focus-visible:outline-offset-2';
                    $errorClasses = $hasError ? 'border-red-500' : 'border-gray-300';
                    $finalClass = $baseClasses . ' ' . $errorClasses;
                @endphp

                <div class="mb-4" wire:key="multistep-field-{{ $field }}">
                    <label for="{{ $inputId }}" class="block mb-1 font-semibold" style="color: {{ $primaryColor }}">
                        {{ $config['label'] }}
                        @if ($isRequired)
                            <span class="text-red-500" aria-hidden="true">*</span>
                        @endif
                    </label>

                    @if ($config['type'] === 'textarea')
                        <textarea
                            id="{{ $inputId }}"
                            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                            @if ($hasError) aria-describedby="{{ $errorId }}" @endif
                            wire:model="formData.{{ $field }}"
                            class="{{ $finalClass }} h-32"
                            @if ($isRequired) required @endif
                        ></textarea>
                    @elseif ($config['type'] === 'select')
                        <select
                            id="{{ $inputId }}"
                            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                            @if ($hasError) aria-describedby="{{ $errorId }}" @endif
                            wire:model="formData.{{ $field }}"
                            class="{{ $finalClass }}"
                            @if ($isRequired) required @endif
                        >
                            @foreach ($config['options'] as $optionValue => $optionLabel)
                                <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    @else
                        <input
                            id="{{ $inputId }}"
                            type="{{ $config['type'] }}"
                            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                            @if ($hasError) aria-describedby="{{ $errorId }}" @endif
                            wire:model="formData.{{ $field }}"
                            class="{{ $finalClass }}"
                            @if ($isRequired) required @endif
                        >
                    @endif

                    @error($errorKey)
                        <span id="{{ $errorId }}" class="text-sm text-red-500 mt-1 block" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
            @endforeach
        </fieldset>
    @else
        <section aria-labelledby="multistep-review-heading">
            <h2 id="multistep-review-heading" class="text-lg font-semibold">Review your information</h2>

            <dl class="mt-4 space-y-4">
                @foreach ($this->reviewItems() as $item)
                    <div wire:key="multistep-review-{{ $item['name'] }}">
                        <dt class="font-semibold">{{ $item['label'] }}</dt>
                        <dd class="whitespace-pre-line">{{ $item['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endif

    <div class="mt-6 flex justify-between items-center gap-4">
        @if ($step > 1)
            <button
                type="button"
                wire:click="previousStep"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded flex items-center focus-visible:outline-2 focus-visible:outline-offset-2"
                wire:loading.attr="disabled"
                wire:target="previousStep"
            >
                Previous step
                <svg
                    wire:loading
                    wire:target="previousStep"
                    class="ml-2 w-4 h-4 motion-safe:animate-spin text-gray-600"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                <span wire:loading wire:target="previousStep" class="sr-only">Loading previous step</span>
            </button>
        @endif

        <div class="ml-auto">
            @if (! $isReviewStep)
                <button
                    type="button"
                    wire:click="nextStep"
                    class="px-4 py-2 text-white rounded flex items-center focus-visible:outline-2 focus-visible:outline-offset-2"
                    style="background-color: {{ $buttonColor }}"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                >
                    Continue
                    <svg
                        wire:loading
                        wire:target="nextStep"
                        class="ml-2 w-4 h-4 motion-safe:animate-spin text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    <span wire:loading wire:target="nextStep" class="sr-only">Loading next step</span>
                </button>
            @else
                <button
                    type="submit"
                    class="px-4 py-2 text-white rounded flex items-center focus-visible:outline-2 focus-visible:outline-offset-2"
                    style="background-color: {{ $buttonColor }}"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                >
                    Submit
                    <svg
                        wire:loading
                        wire:target="submit"
                        class="ml-2 w-4 h-4 motion-safe:animate-spin text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    <span wire:loading wire:target="submit" class="sr-only">Submitting form</span>
                </button>
            @endif
        </div>
    </div>
</form>
