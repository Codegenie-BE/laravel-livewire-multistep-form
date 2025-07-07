@extends('layouts.app')

@section('title', 'Livewire Multi-Step Wizard')
@section('subtitle', 'This form uses Livewire and Tailwind, and is fully validated.')

@section('content')

    @livewire('multi-step-form', [
        'primaryColor' => '#2563eb',
        'buttonColor' => '#2563eb',
        'fields' => [
            'name' => [
                'default' => '',
                'rules' => 'required|min:2',
                'label' => 'Name',
                'step' => 1,
                'type' => 'text',
            ],
            'email' => [
                'default' => '',
                'rules' => 'required|email',
                'label' => 'Email',
                'step' => 2,
                'type' => 'email',
            ],
            'message' => [
                'default' => '',
                'rules' => 'required|min:10',
                'label' => 'Message',
                'step' => 3,
                'type' => 'textarea',
            ],
        ],
    ])
@endsection