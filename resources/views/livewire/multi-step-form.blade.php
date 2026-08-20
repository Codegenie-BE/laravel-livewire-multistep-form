@php
    $stepFields = $this->currentStepFields();
    $totalSteps = $this->totalSteps();
    $isReviewStep = $this->isReviewStep();
@endphp

<div class="max-w-xl mx-auto bg-white p-6 rounded shadow-md">
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-800">
                Step {{ $step }} from {{ $totalSteps }}
            </span>
        </div>

        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
            <div
                class="h-full rounded-full transition-all duration-300"
                style="width: {{ ($step / $totalSteps) * 100 }}%; background-color: {{ $primaryColor }}"
            ></div>
        </div>
    </div>

    @if (! $isReviewStep)
        @foreach ($stepFields as $field => $config)
            @php
                $inputId = 'field-' . $field;
                $errorId = 'error-' . $field;
                $errorKey = 'formData.' . $field;
                $hasError = $errors->has($errorKey);
                $isRequired = $this->isFieldRequired($config);
                $baseClasses = 'w-full border p-2 rounded';
                $errorClasses = $hasError ? 'border-red-500' : 'border-gray-300';
                $finalClass = $baseClasses . ' ' . $errorClasses;
            @endphp

            <div class="mb-4" wire:key="multistep-field-{{ $field }}">
                <label for="{{ $inputId }}" class="block mb-1 font-semibold" style="color: {{ $primaryColor }}">
                    {{ $config['label'] }}
                    @if ($isRequired)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @if ($config['type'] === 'textarea')
                    <textarea
                        id="{{ $inputId }}"
                        aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                        @if ($hasError) aria-describedby="{{ $errorId }}" @endif
                        wire:model.defer="formData.{{ $field }}"
                        class="{{ $finalClass }} h-32"
                        @if ($isRequired) required @endif
                    ></textarea>
                @elseif ($config['type'] === 'select')
                    <select
                        id="{{ $inputId }}"
                        aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                        @if ($hasError) aria-describedby="{{ $errorId }}" @endif
                        wire:model.defer="formData.{{ $field }}"
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
                        wire:model.defer="formData.{{ $field }}"
                        class="{{ $finalClass }}"
                        @if ($isRequired) required @endif
                    >
                @endif

                @error($errorKey)
                    <span id="{{ $errorId }}" class="text-sm text-red-500 mt-1 block" aria-live="polite">
                        {{ $message }}
                    </span>
                @enderror
            </div>
        @endforeach
    @else
        <div class="space-y-4">
            <h2 class="text-lg font-semibold">Review your information</h2>

            @foreach ($this->reviewItems() as $item)
                <div wire:key="multistep-review-{{ $item['name'] }}">
                    <strong>{{ $item['label'] }}:</strong>
                    <span class="whitespace-pre-line">{{ $item['value'] }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-6 flex justify-between items-center space-x-4">
        @if ($step > 1)
            <button
                wire:click="previousStep"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded flex items-center"
                wire:loading.attr="disabled"
                wire:target="previousStep"
            >
                Previous step
                <svg wire:loading wire:target="previousStep" class="ml-2 w-4 h-4 animate-spin text-gray-600"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
            </button>
        @endif

        <div class="ml-auto">
            @if (! $isReviewStep)
                <button
                    wire:click="nextStep"
                    class="px-4 py-2 text-white rounded flex items-center"
                    style="background-color: {{ $buttonColor }}"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                >
                    Continue
                    <svg wire:loading wire:target="nextStep" class="ml-2 w-4 h-4 animate-spin text-white"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                </button>
            @else
                <button
                    wire:click="submit"
                    class="px-4 py-2 text-white rounded flex items-center"
                    style="background-color: {{ $buttonColor }}"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                >
                    Submit
                    <svg wire:loading wire:target="submit" class="ml-2 w-4 h-4 animate-spin text-white"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                </button>
            @endif
        </div>
    </div>
</div>
