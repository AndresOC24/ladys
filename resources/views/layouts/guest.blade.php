<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', "Lady's On Go") }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased">
        <div class="min-h-dvh flex flex-col sm:justify-center items-center pt-10 sm:pt-0 bg-gradient-to-br from-primary-50 via-white to-accent-50 px-4">
            <a href="{{ route('login') }}" class="flex flex-col items-center gap-2 group" aria-label="Lady's On Go">
                <span class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 text-white shadow-lg shadow-primary-200 group-hover:scale-105 transition duration-200 ease-out">
                    <x-application-logo class="w-9 h-9" />
                </span>
                <span class="text-2xl font-extrabold tracking-tight text-primary-900">Lady's <span class="text-primary-500">On Go</span></span>
                <span class="text-xs font-medium text-slate-500">Transporte seguro, identidad verificada</span>
            </a>

            <div class="w-full sm:max-w-md mt-8 px-7 py-8 bg-white border border-primary-100 shadow-xl shadow-primary-100/50 overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>

            <p class="mt-6 mb-8 text-xs text-slate-400">© {{ date('Y') }} Lady's On Go — Santa Cruz de la Sierra</p>
        </div>
    </body>
</html>
