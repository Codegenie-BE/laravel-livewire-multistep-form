<div class="max-w-xl mx-auto bg-white p-6 rounded shadow-md">
    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if ($step === 1)
        <div>
            <label class="block mb-1 font-semibold" style="color: {{ $primaryColor }}">Name</label>
            <input type="text" wire:model.defer="name" class="w-full border p-2 rounded" />
            @error('name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
        </div>
    @elseif ($step === 2)
        <div>
            <label class="block mb-1 font-semibold" style="color: {{ $primaryColor }}">Email</label>
            <input type="email" wire:model.defer="email" class="w-full border p-2 rounded" />
            @error('email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
        </div>
    @elseif ($step === 3)
        <div>
            <label class="block mb-1 font-semibold" style="color: {{ $primaryColor }}">Message</label>
            <textarea wire:model.defer="message" class="w-full border p-2 rounded h-32"></textarea>
            @error('message') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
        </div>
    @endif

    <div class="mt-6 flex justify-between items-center space-x-4">
        @if ($step > 1)
            <div class="relative">
                <button wire:click="previousStep"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded flex items-center"
                        wire:loading.attr="disabled"
                        wire:target="previousStep">
                    Back
                    <svg wire:loading wire:target="previousStep" class="ml-2 w-4 h-4 animate-spin text-gray-600"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </button>
            </div>
        @endif

        <div class="ml-auto relative">
            @if ($step < 3)
                <button wire:click="nextStep"
                        class="px-4 py-2 text-white rounded flex items-center"
                        style="background-color: {{ $buttonColor }}"
                        wire:loading.attr="disabled"
                        wire:target="nextStep">
                    Next
                    <svg wire:loading wire:target="nextStep" class="ml-2 w-4 h-4 animate-spin text-white"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </button>
            @else
                <button wire:click="submit"
                        class="px-4 py-2 text-white rounded flex items-center"
                        style="background-color: {{ $buttonColor }}"
                        wire:loading.attr="disabled"
                        wire:target="submit">
                    Submit
                    <svg wire:loading wire:target="submit" class="ml-2 w-4 h-4 animate-spin text-white"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </button>
            @endif
        </div>
    </div>
</div>
