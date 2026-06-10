<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Verificación de identidad — Paso 1') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @include('verificacion._pasos', ['actual' => 1])

            <div class="bg-white p-6 rounded-2xl border border-primary-100 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900">{{ __('Confirma tus datos personales') }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Estos datos se contrastarán con la información extraída de tu cédula de identidad.') }}
                </p>

                <form method="POST" action="{{ route('verificacion.paso1.guardar') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Nombre completo')" />
                        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full"
                            :value="old('name', $usuaria->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="telefono" :value="__('Teléfono')" />
                        <x-text-input id="telefono" name="telefono" type="tel" class="block mt-1 w-full"
                            :value="old('telefono', $usuaria->telefono)" required />
                        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="fecha_nacimiento" :value="__('Fecha de nacimiento')" />
                        <x-text-input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="block mt-1 w-full"
                            :value="old('fecha_nacimiento', $usuaria->fecha_nacimiento?->toDateString())" required />
                        <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
                    </div>

                    <div class="flex justify-end pt-4">
                        <x-primary-button>{{ __('Continuar') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
