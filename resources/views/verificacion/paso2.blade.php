<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Verificación de identidad — Paso 2') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @include('verificacion._pasos', ['actual' => 2])

            <div class="bg-white p-6 rounded-2xl border border-primary-100 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900">{{ __('Sube tu cédula de identidad') }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Fotografías nítidas, sin reflejos y con todos los datos legibles. Formatos JPG o PNG, máximo 5 MB por imagen.') }}
                </p>

                <form method="POST" action="{{ route('verificacion.paso2.guardar') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach (['anverso' => 'Anverso (frente)', 'reverso' => 'Reverso (atrás)'] as $tipo => $etiqueta)
                            <div x-data="{ preview: null }">
                                <x-input-label :for="$tipo" :value="__($etiqueta)" />
                                <label :class="preview ? 'border-green-400' : 'border-gray-300'"
                                    class="mt-1 flex flex-col items-center justify-center w-full h-44 border-2 border-dashed rounded-md cursor-pointer bg-gray-50 hover:bg-gray-100 overflow-hidden">
                                    <template x-if="preview">
                                        <img :src="preview" class="object-contain w-full h-full" alt="Vista previa {{ $tipo }}">
                                    </template>
                                    <template x-if="!preview">
                                        <span class="flex flex-col items-center gap-2 text-sm text-slate-500 px-4 text-center">
                                            <x-icono nombre="camara" class="w-8 h-8 text-primary-400" />
                                            {{ __('Haz clic para seleccionar la imagen') }}
                                        </span>
                                    </template>
                                    <input id="{{ $tipo }}" name="{{ $tipo }}" type="file" accept="image/jpeg,image/png" class="hidden" required
                                        @change="const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
                                </label>
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
</x-app-layout>
