<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Verificación de identidad') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-10 rounded-md shadow-sm text-center"
                x-data="pollingEstado('{{ route('verificacion.estado-json') }}', '{{ route('verificacion.resultado') }}')">

                <div class="mx-auto w-16 h-16 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>

                <h3 class="mt-6 text-lg font-bold text-gray-900">{{ __('Estamos verificando tu identidad') }}</h3>
                <p class="mt-2 text-sm text-gray-600">
                    {{ __('Comparando tu rostro con la fotografía del documento y validando los datos de tu cédula. Esto toma solo unos segundos, no cierres esta página.') }}
                </p>
            </div>
        </div>
    </div>

    <script>
        function pollingEstado(urlEstado, urlResultado) {
            return {
                init() {
                    this.intervalo = setInterval(async () => {
                        try {
                            const respuesta = await fetch(urlEstado, { headers: { 'Accept': 'application/json' } });
                            const datos = await respuesta.json();
                            if (datos.estado_usuaria !== 'en_proceso') {
                                clearInterval(this.intervalo);
                                window.location.href = urlResultado;
                            }
                        } catch (e) {
                            // Error transitorio de red: se reintenta en el siguiente ciclo.
                        }
                    }, 3000);
                },
            };
        }
    </script>
</x-app-layout>
