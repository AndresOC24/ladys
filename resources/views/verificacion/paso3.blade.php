<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Verificación de identidad — Paso 3') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @include('verificacion._pasos', ['actual' => 3])

            <div class="bg-white p-6 rounded-md shadow-sm"
                x-data="capturaFacial('{{ route('verificacion.paso3.guardar') }}', '{{ csrf_token() }}')">

                <h3 class="text-lg font-bold text-gray-900">{{ __('Captura de tu rostro en vivo') }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Ubica tu rostro dentro del recuadro, con buena iluminación y sin lentes oscuros ni gorra. La foto se compara con la de tu cédula.') }}
                </p>

                <div class="mt-6 flex flex-col items-center gap-4">
                    <template x-if="error">
                        <div class="w-full p-4 bg-red-50 border-l-4 border-red-400 text-red-800 text-sm rounded-md" x-text="error"></div>
                    </template>

                    <div class="relative w-full max-w-md aspect-[4/3] bg-black rounded-md overflow-hidden">
                        <video x-show="!foto" x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                        <img x-show="foto" :src="foto" class="w-full h-full object-cover" alt="Captura">
                        <div x-show="!foto" class="absolute inset-0 pointer-events-none border-[3px] border-white/60 rounded-[50%] m-10"></div>
                    </div>
                    <canvas x-ref="canvas" class="hidden"></canvas>

                    <div class="flex gap-3">
                        <button type="button" x-show="!foto" @click="capturar()" :disabled="!listo"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 disabled:opacity-50 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-500">
                            📸 {{ __('Capturar') }}
                        </button>
                        <button type="button" x-show="foto && !enviando" @click="repetir()"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-300">
                            ↺ {{ __('Repetir') }}
                        </button>
                        <button type="button" x-show="foto" @click="enviar()" :disabled="enviando"
                            class="inline-flex items-center px-4 py-2 bg-green-600 disabled:opacity-50 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-500">
                            <span x-show="!enviando">✓ {{ __('Enviar y verificar') }}</span>
                            <span x-show="enviando">{{ __('Enviando…') }}</span>
                        </button>
                    </div>
                </div>

                <div class="flex justify-between pt-6">
                    <a href="{{ route('verificacion.paso2') }}" class="text-sm text-gray-600 underline">{{ __('Volver') }}</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function capturaFacial(urlEnvio, csrf) {
            return {
                stream: null,
                listo: false,
                foto: null,
                blob: null,
                enviando: false,
                error: null,

                async init() {
                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 960 } },
                            audio: false,
                        });
                        this.$refs.video.srcObject = this.stream;
                        this.listo = true;
                    } catch (e) {
                        this.error = 'No se pudo acceder a la cámara. Verifica los permisos del navegador e intenta de nuevo.';
                    }
                },

                capturar() {
                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    canvas.toBlob((blob) => {
                        this.blob = blob;
                        this.foto = URL.createObjectURL(blob);
                    }, 'image/jpeg', 0.92);
                },

                repetir() {
                    this.foto = null;
                    this.blob = null;
                    this.error = null;
                },

                async enviar() {
                    if (!this.blob) return;
                    this.enviando = true;
                    this.error = null;

                    const datos = new FormData();
                    datos.append('_token', csrf);
                    datos.append('selfie', this.blob, 'selfie.jpg');

                    try {
                        const respuesta = await fetch(urlEnvio, { method: 'POST', body: datos });
                        if (respuesta.redirected) {
                            this.stream?.getTracks().forEach(t => t.stop());
                            window.location.href = respuesta.url;
                            return;
                        }
                        if (!respuesta.ok) {
                            const cuerpo = await respuesta.json().catch(() => null);
                            this.error = cuerpo?.message ?? 'Ocurrió un error al enviar la captura. Intenta nuevamente.';
                        }
                    } catch (e) {
                        this.error = 'Error de conexión al enviar la captura. Intenta nuevamente.';
                    } finally {
                        this.enviando = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>
