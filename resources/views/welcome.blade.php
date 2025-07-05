@extends('layouts.app')

@section('content')

    @section('title', 'Livewire Multi-Step Wizard')
    @section('subtitle', 'This form uses Livewire and Tailwind, and is fully validated.')

    {{-- Livewire multi-step component --}}
    @livewire('multi-step-form', [
        'primaryColor' => '#2563eb',    // used for titles, text
        'buttonColor' => '#2563eb',     // shared for both Next and Submit buttons
    ])
@endsection