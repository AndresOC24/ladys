<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Resultado de la verificación') }}
        </h2>
    </x-slot>

    @php
        $config = match ($usuaria->estado_verificacion) {
            'aprobada' => [
                'titulo' => '¡Identidad verificada!',
                'mensaje' => 'Tu identidad fue validada correctamente. Ya puedes usar el servicio.',
                'color' => 'bg-green-50 border-green-400 text-green-800',
                'icono' => 'aprobado',
            ],
            'rechazada' => [
                'titulo' => 'Verificación rechazada',
                'mensaje' => 'No pudimos validar tu identidad. Revisa los motivos e inténtalo de nuevo.',
                'color' => 'bg-red-50 border-red-400 text-red-800',
                'icono' => 'rechazo',
            ],
            'en_revision' => [
                'titulo' => 'Tu caso está en revisión',
                'mensaje' => 'La verificación automática no fue concluyente y una administradora revisará tu caso manualmente. Tiempo máximo de respuesta: 48 horas.',
                'color' => 'bg-purple-50 border-purple-400 text-purple-800',
                'icono' => 'lupa',
            ],
            default => [
                'titulo' => 'Verificación pendiente',
                'mensaje' => 'Tu proceso de verificación aún no ha concluido.',
                'color' => 'bg-yellow-50 border-yellow-400 text-yellow-800',
                'icono' => 'reloj',
            ],
        };
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="border-l-4 p-6 rounded-2xl shadow-sm {{ $config['color'] }}">
                <div class="flex items-start gap-4">
                    <x-icono :nombre="$config['icono']" class="w-8 h-8 shrink-0" />
                    <div>
                        <h3 class="text-lg font-bold">{{ $config['titulo'] }}</h3>
                        <p class="mt-2">{{ $config['mensaje'] }}</p>
                    </div>
                </div>
            </div>

            @if ($motivos->isNotEmpty())
                <div class="mt-6 bg-white p-6 rounded-2xl border border-primary-100 shadow-sm">
                    <h4 class="font-semibold text-gray-800">{{ __('Motivos') }}</h4>
                    <ul class="mt-3 list-disc list-inside text-sm text-gray-600 space-y-1">
                        @foreach ($motivos as $motivo)
                            <li>{{ $motivo }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-6 flex gap-3">
                @if ($usuaria->estado_verificacion === 'aprobada')
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-accent-500 rounded-xl font-semibold text-sm text-white shadow-sm shadow-primary-200 hover:from-primary-600 hover:to-accent-600 transition duration-200 cursor-pointer">
                        {{ __('Ir al panel') }}
                    </a>
                @elseif ($usuaria->estado_verificacion === 'rechazada')
                    <a href="{{ route('verificacion.paso1') }}"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-accent-500 rounded-xl font-semibold text-sm text-white shadow-sm shadow-primary-200 hover:from-primary-600 hover:to-accent-600 transition duration-200 cursor-pointer">
                        {{ __('Intentar nuevamente') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
