@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto mt-20 text-center">
        {{-- Success Alert --}}
        <div class="mb-6 p-6 bg-green-50 text-green-800 border border-green-300 rounded-lg shadow-sm">
            <div class="flex items-center justify-center mb-2">
                <span class="text-4xl mr-2">✅</span>
                <h1 class="text-2xl font-semibold">
                    {{ config('ui.thankyou.title') }}
                </h1>
            </div>
            <p class="text-gray-700 text-base">
                {{ config('ui.thankyou.success_message') }}
            </p>
        </div>

        {{-- Extra Confirmation Text --}}
        <p class="text-gray-600 mt-4">
            {{ config('ui.thankyou.subtitle') }}
        </p>

        {{-- Call to Action --}}
        <div class="mt-8">
            <a href="{{ route('home') }}"
               class="inline-block px-6 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition">
                {{ config('ui.thankyou.cta_label') }}
            </a>
        </div>
    </div>
@endsection