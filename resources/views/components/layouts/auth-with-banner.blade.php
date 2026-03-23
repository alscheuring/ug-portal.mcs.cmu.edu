<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'CMU UG Portal') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-white">
    <!-- Use Flux's auth layout content here -->
    <x-layouts::auth :title="$title ?? null">
        {{ $slot }}
    </x-layouts::auth>

    <!-- Impersonation Banner -->
    <x-impersonate-banner/>

    @stack('scripts')
</body>
</html>