<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Verificación de identidad') }}
        </h2>
    </x-slot>

    @php
        $estados = [
            'pendiente' => [
                'titulo' => 'Verificación pendiente',
                'mensaje' => 'Aún no has iniciado tu proceso de verificación de identidad. Para acceder al servicio necesitas validar tu cédula de identidad y tu rostro.',
                'color' => 'bg-yellow-50 border-yellow-400 text-yellow-800',
                'icono' => 'reloj',
            ],
            'en_proceso' => [
                'titulo' => 'Verificación en proceso',
                'mensaje' => 'Tu verificación está siendo procesada por el sistema. Esto normalmente toma unos segundos; vuelve a cargar esta página en un momento.',
                'color' => 'bg-blue-50 border-blue-400 text-blue-800',
                'icono' => 'proceso',
            ],
            'en_revision' => [
                'titulo' => 'En revisión administrativa',
                'mensaje' => 'La verificación automática no fue concluyente y tu caso fue derivado a una revisión manual por parte de nuestro equipo. Te notificaremos el resultado; el tiempo máximo de respuesta es de 48 horas.',
                'color' => 'bg-purple-50 border-purple-400 text-purple-800',
                'icono' => 'lupa',
            ],
            'rechazada' => [
                'titulo' => 'Verificación rechazada',
                'mensaje' => 'Tu verificación fue rechazada. Revisa las observaciones y vuelve a intentarlo con imágenes claras de tu cédula y tu rostro.',
                'color' => 'bg-red-50 border-red-400 text-red-800',
                'icono' => 'rechazo',
            ],
        ];
        $info = $estados[$usuaria->estado_verificacion] ?? $estados['pendiente'];
    @endphp

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="border-l-4 p-6 rounded-2xl shadow-sm {{ $info['color'] }}">
                <div class="flex items-start gap-4">
                    <x-icono :nombre="$info['icono']" class="w-8 h-8 shrink-0" />
                    <div>
                        <h3 class="text-lg font-bold">{{ $info['titulo'] }}</h3>
                        <p class="mt-2">{{ $info['mensaje'] }}</p>

                        @if ($registro)
                            <p class="mt-4 text-sm opacity-75">
                                {{ __('Último registro de verificación iniciado el') }}
                                {{ $registro->fecha_inicio?->format('d/m/Y H:i') ?? $registro->created_at->format('d/m/Y H:i') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            @if (in_array($usuaria->estado_verificacion, ['pendiente', 'rechazada']))
                <div class="mt-6">
                    <a href="{{ route('verificacion.paso1') }}"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-accent-500 rounded-xl font-semibold text-sm text-white shadow-sm shadow-primary-200 hover:from-primary-600 hover:to-accent-600 transition duration-200 cursor-pointer">
                        {{ $usuaria->estado_verificacion === 'rechazada' ? __('Intentar nuevamente') : __('Iniciar verificación') }}
                    </a>
                </div>
            @elseif ($usuaria->estado_verificacion === 'en_proceso')
                <div class="mt-6">
                    <a href="{{ route('verificacion.procesando') }}" class="text-sm text-primary-600 underline">
                        {{ __('Ver progreso de la verificación') }}
                    </a>
                </div>
            @endif

            <div class="mt-6 bg-white p-6 rounded-2xl border border-primary-100 shadow-sm">
                <h4 class="font-semibold text-gray-800">{{ __('Tus datos') }}</h4>
                <dl class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-600">
                    <div><dt class="font-medium inline">{{ __('Nombre:') }}</dt> <dd class="inline">{{ $usuaria->name }}</dd></div>
                    <div><dt class="font-medium inline">{{ __('Rol:') }}</dt> <dd class="inline">{{ ucfirst($usuaria->rol?->nombre ?? '—') }}</dd></div>
                    <div><dt class="font-medium inline">{{ __('Correo:') }}</dt> <dd class="inline">{{ $usuaria->email }}</dd></div>
                    <div><dt class="font-medium inline">{{ __('Estado:') }}</dt> <dd class="inline">{{ str_replace('_', ' ', $usuaria->estado_verificacion) }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
