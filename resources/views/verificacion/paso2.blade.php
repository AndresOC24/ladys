<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Verificación de identidad — Paso 2') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('verificacion._pasos', ['actual' => 2])

            <div class="bg-white p-6 rounded-2xl border border-primary-100 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900">{{ __('Captura tu cédula de identidad') }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Toma una foto con tu cámara o sube una imagen de cada lado. Sin reflejos, con todos los datos legibles (incluida la zona de caracteres del reverso). JPG/PNG, máx. 5 MB.') }}
                </p>

                <form method="POST" action="{{ route('verificacion.paso2.guardar') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach (['anverso' => 'Anverso (frente, con tu fotografía)', 'reverso' => 'Reverso (atrás, con el código y la zona de caracteres)'] as $tipo => $etiqueta)
                            <div x-data="capturaDocumento('{{ $tipo }}')">
                                <x-input-label :for="$tipo" :value="__($etiqueta)" />

                                {{-- Vista: cámara activa --}}
                                <div x-show="modo === 'camara'" x-cloak class="mt-1">
                                    <div class="relative w-full aspect-[8/5] bg-black rounded-xl overflow-hidden">
                                        <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                                        <div class="absolute inset-3 pointer-events-none border-2 border-dashed border-white/70 rounded-lg"></div>
                                    </div>
                                    <div class="flex gap-2 mt-2">
                                        <button type="button" @click="capturar()" :disabled="!listo"
                                            class="inline-flex items-center gap-1.5 min-h-[44px] px-4 py-2 bg-gradient-to-r from-primary-500 to-accent-500 disabled:opacity-50 rounded-xl font-semibold text-sm text-white shadow-sm shadow-primary-200 hover:from-primary-600 hover:to-accent-600 transition duration-200 cursor-pointer">
                                            <x-icono nombre="camara" class="w-4 h-4" /> {{ __('Capturar') }}
                                        </button>
                                        <button type="button" @click="cerrarCamara()"
                                            class="inline-flex items-center min-h-[44px] px-4 py-2 bg-white border border-primary-200 rounded-xl font-semibold text-sm text-slate-700 hover:bg-primary-50 transition duration-200 cursor-pointer">
                                            {{ __('Cancelar') }}
                                        </button>
                                    </div>
                                </div>

                                {{-- Vista: preview / selección --}}
                                <div x-show="modo !== 'camara'" class="mt-1">
                                    <div :class="preview ? 'border-green-400' : 'border-primary-200'"
                                        class="flex flex-col items-center justify-center w-full aspect-[8/5] border-2 border-dashed rounded-xl bg-primary-50/40 overflow-hidden">
                                        <template x-if="preview">
                                            <img :src="preview" class="object-contain w-full h-full bg-white" alt="Vista previa {{ $tipo }}">
                                        </template>
                                        <template x-if="!preview">
                                            <span class="flex flex-col items-center gap-2 text-sm text-slate-500 px-4 text-center">
                                                <x-icono nombre="documento" class="w-9 h-9 text-primary-400" />
                                                {{ __('Aún sin imagen') }}
                                            </span>
                                        </template>
                                    </div>

                                    <div class="flex gap-2 mt-2">
                                        <button type="button" @click="abrirCamara()"
                                            class="inline-flex items-center gap-1.5 min-h-[44px] px-4 py-2 bg-gradient-to-r from-primary-500 to-accent-500 rounded-xl font-semibold text-sm text-white shadow-sm shadow-primary-200 hover:from-primary-600 hover:to-accent-600 transition duration-200 cursor-pointer">
                                            <x-icono nombre="camara" class="w-4 h-4" /> <span x-text="preview ? '{{ __('Repetir foto') }}' : '{{ __('Tomar foto') }}'"></span>
                                        </button>
                                        <button type="button" @click="$refs.archivo.click()"
                                            class="inline-flex items-center min-h-[44px] px-4 py-2 bg-white border border-primary-200 rounded-xl font-semibold text-sm text-slate-700 hover:bg-primary-50 transition duration-200 cursor-pointer">
                                            {{ __('Subir archivo') }}
                                        </button>
                                    </div>

                                    <p x-show="error" x-text="error" class="mt-2 text-sm text-red-600"></p>
                                </div>

                                <input id="{{ $tipo }}" name="{{ $tipo }}" type="file" accept="image/jpeg,image/png"
                                    class="hidden" required x-ref="archivo"
                                    @change="const f = $event.target.files[0]; if (f) { preview = URL.createObjectURL(f); error = null; }">
                                <canvas x-ref="canvas" class="hidden"></canvas>

                                <x-input-error :messages="$errors->get($tipo)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-between pt-4">
                        <a href="{{ route('verificacion.paso1') }}" class="text-sm text-gray-600 underline self-center">
                            {{ __('Volver') }}
                        </a>
                        <x-primary-button>{{ __('Continuar') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function capturaDocumento(tipo) {
            return {
                modo: 'seleccion',
                stream: null,
                listo: false,
                preview: null,
                error: null,

                async abrirCamara() {
                    this.error = null;
                    this.modo = 'camara';
                    try {
                        // Cámara trasera si existe (móvil): es la adecuada para documentos.
                        this.stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1200 } },
                            audio: false,
                        });
                        this.$refs.video.srcObject = this.stream;
                        this.listo = true;
                    } catch (e) {
                        this.modo = 'seleccion';
                        this.error = 'No se pudo acceder a la cámara. Puedes subir un archivo en su lugar.';
                    }
                },

                capturar() {
                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    canvas.toBlob((blob) => {
                        // Inyecta la captura en el input file: el formulario se
                        // envía igual que con un archivo subido manualmente.
                        const archivo = new File([blob], `${tipo}.jpg`, { type: 'image/jpeg' });
                        const dt = new DataTransfer();
                        dt.items.add(archivo);
                        this.$refs.archivo.files = dt.files;

                        this.preview = URL.createObjectURL(blob);
                        this.cerrarCamara();
                    }, 'image/jpeg', 0.92);
                },

                cerrarCamara() {
                    this.stream?.getTracks().forEach(t => t.stop());
                    this.stream = null;
                    this.listo = false;
                    this.modo = 'seleccion';
                },
            };
        }
    </script>
</x-app-layout>
