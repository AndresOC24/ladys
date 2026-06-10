<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Panel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden border border-primary-100 shadow-sm rounded-2xl">
                <div class="p-8 flex items-start gap-4">
                    <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-green-100 text-green-600 shrink-0">
                        <x-icono nombre="escudo" class="w-7 h-7" />
                    </span>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            {{ __('¡Hola, :nombre!', ['nombre' => auth()->user()->name]) }}
                        </h3>
                        <p class="mt-1 text-slate-600">
                            {{ __('Tu identidad está verificada. Ya formas parte de la comunidad de Lady\'s On Go como :rol.', ['rol' => auth()->user()->rol?->nombre ?? 'usuaria']) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
