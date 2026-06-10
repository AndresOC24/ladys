<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Verificación de identidad') }}
        </h2>
    </x-slot>

    @php
        $estados = [
            'pendiente' => [
                'titulo' => 'Verificación pendiente',
                'mensaje' => 'Aún no has iniciado tu proceso de verificación de identidad. Para acceder al servicio necesitas validar tu cédula de identidad y tu rostro.',
                'color' => 'bg-yellow-50 border-yellow-400 text-yellow-800',
                'icono' => '⏳',
            ],
            'en_proceso' => [
                'titulo' => 'Verificación en proceso',
                'mensaje' => 'Tu verificación está siendo procesada por el sistema. Esto normalmente toma unos segundos; vuelve a cargar esta página en un momento.',
                'color' => 'bg-blue-50 border-blue-400 text-blue-800',
                'icono' => '🔄',
            ],
            'en_revision' => [
                'titulo' => 'En revisión administrativa',
                'mensaje' => 'La verificación automática no fue concluyente y tu caso fue derivado a una revisión manual por parte de nuestro equipo. Te notificaremos el resultado; el tiempo máximo de respuesta es de 48 horas.',
                'color' => 'bg-purple-50 border-purple-400 text-purple-800',
                'icono' => '🔍',
            ],
            'rechazada' => [
                'titulo' => 'Verificación rechazada',
                'mensaje' => 'Tu verificación fue rechazada. Revisa las observaciones y vuelve a intentarlo con imágenes claras de tu cédula y tu rostro.',
                'color' => 'bg-red-50 border-red-400 text-red-800',
                'icono' => '❌',
            ],
        ];
        $info = $estados[$usuaria->estado_verificacion] ?? $estados['pendiente'];
    @endphp

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="border-l-4 p-6 rounded-md shadow-sm {{ $info['color'] }}">
                <div class="flex items-start gap-4">
                    <span class="text-3xl">{{ $info['icono'] }}</span>
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

            <div class="mt-6 bg-white p-6 rounded-md shadow-sm">
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
