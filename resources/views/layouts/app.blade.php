<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{config('app.name')}}</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-800 antialiased font-sans min-h-screen flex flex-col">

    {{-- Hero Title & Subtitle --}}
    <header class="text-center mb-10 mt-8 px-4">
        <h1 class="text-4xl font-bold mb-4 text-blue-600">
            @yield('title', 'Livewire Multi-Step Wizard')
        </h1>
        <p class="text-lg text-gray-700 max-w-2xl mx-auto">
            @yield('subtitle', 'This form uses Livewire and Tailwind, and is fully validated.')
        </p>
    </header>

    {{-- Main Content --}}
    <main class="flex-grow container mx-auto px-4">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="text-center text-sm text-gray-500 py-4">
        &copy; {{ date('Y') }} {{ config('app.name', 'Laravel App') }}
    </footer>

    @livewireScripts
</body>
</html>
