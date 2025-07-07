@php
    $stepFields = collect($this->fields)->filter(fn($config) => $config['step'] === $step);
    $totalSteps = collect($this->fields)->pluck('step')->unique()->count()+1;
@endphp

<div class="max-w-xl mx-auto bg-white p-6 rounded shadow-md">
    @if ($step <= $totalSteps)
        {{-- STEP INDICATOR - PROGRESS BAR --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-gray-800">
                    Step {{ $step }} from {{ $totalSteps }}
                </span>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div class="h-full rounded-full transition-all duration-300"
                    style="width: {{ ($step / $totalSteps) * 100 }}%; background-color: {{ $primaryColor ?? '#3b82f6' }}">
                </div>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if ($stepFields->isNotEmpty())
        @foreach ($stepFields as $field => $config)
            @php
                $label = $config['label'] ?? ucfirst($field);
                $inputId = 'field-' . $field;
                $errorId = 'error-' . $field;
                $hasError = $errors->has($field);
                $baseClasses = 'w-full border p-2 rounded';
                $errorClasses = $hasError ? 'border-red-500' : 'border-gray-300';
                $finalClass = $baseClasses . ' ' . $errorClasses;
                $isRequired = str_contains($config['rules'] ?? '', 'required') ? 'required' : null;
            @endphp

            <div class="mb-4">
                <label for="{{ $inputId }}" class="block mb-1 font-semibold" style="color: {{ $primaryColor }}">
                    {{ $label }}
                    @if($isRequired) <span class="text-red-500">*</span> @endif
                </label>

                @if ($config['type'] === 'textarea')
                    <textarea
                        id="{{ $inputId }}"
                        aria-label="{{ $label }}"
                        aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                        @if($hasError) aria-describedby="{{ $errorId }}" @endif
                        wire:model.defer="formData.{{ $field }}"
                        class="{{ $finalClass }} h-32"
                        {{ $isRequired }}
                    ></textarea>
                @elseif ($config['type'] === 'select')
                    <select
                        id="{{ $inputId }}"
                        aria-label="{{ $label }}"
                        aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                        @if($hasError) aria-describedby="{{ $errorId }}" @endif
                        wire:model.defer="formData.{{ $field }}"
                        class="{{ $finalClass }}"
                        {{ $isRequired }}
                    >
                        @foreach ($config['options'] ?? [] as $optionValue => $optionLabel)
                            <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                @else
                    <input
                        id="{{ $inputId }}"
                        type="{{ $config['type'] }}"
                        aria-label="{{ $label }}"
                        aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                        @if($hasError) aria-describedby="{{ $errorId }}" @endif
                        wire:model.defer="formData.{{ $field }}"
                        class="{{ $finalClass }}"
                        {{ $isRequired }}
                    />
                @endif

                @error($field)
                    <span id="{{ $errorId }}" class="text-sm text-red-500 mt-1 block" aria-live="polite">
                        {{ $message }}
                    </span>
                @enderror
            </div>
        @endforeach


    @elseif ($step === 4)
        {{-- OVERVIEW OF ALL FILLED IN FIELDS --}}
        <div class="space-y-4">
            <h2 class="text-lg font-semibold">Review your information</h2>
            @foreach ($this->getFormData() as $label => $value)
                <div>
                    <strong>{{ $label }}:</strong>
                    {!! is_array($value) ? implode(', ', $value) : nl2br(e($value)) !!}
                </div>
            @endforeach
        </div>
    @endif

    {{-- NAVIGATION BUTTONS --}}
    @if ($step < 5)
        <div class="mt-6 flex justify-between items-center space-x-4">
            @if ($step > 1)
                <button wire:click="previousStep"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded flex items-center"
                        wire:loading.attr="disabled" wire:target="previousStep">
                    Previous step
                    <svg wire:loading wire:target="previousStep" class="ml-2 w-4 h-4 animate-spin text-gray-600"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                </button>
            @endif

            <div class="ml-auto">
                @if ($step < 4)
                    <button wire:click="nextStep"
                            class="px-4 py-2 text-white rounded flex items-center"
                            style="background-color: {{ $buttonColor }}"
                            wire:loading.attr="disabled" wire:target="nextStep">
                        Continue
                        <svg wire:loading wire:target="nextStep" class="ml-2 w-4 h-4 animate-spin text-white"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                    </button>
                @elseif ($step === 4)
                    <button wire:click="submit"
                            class="px-4 py-2 text-white rounded flex items-center"
                            style="background-color: {{ $buttonColor }}"
                            wire:loading.attr="disabled" wire:target="submit">
                        Submit
                        <svg wire:loading wire:target="submit" class="ml-2 w-4 h-4 animate-spin text-white"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    @endif

</div>