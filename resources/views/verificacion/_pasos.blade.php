@props(['actual' => 1])

<div class="flex items-center justify-center gap-2 mb-8">
    @foreach ([1 => 'Datos personales', 2 => 'Cédula de identidad', 3 => 'Captura facial'] as $numero => $etiqueta)
        <div class="flex items-center gap-2">
            <span class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                {{ $numero < $actual ? 'bg-green-500 text-white' : ($numero == $actual ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500') }}">
                {{ $numero < $actual ? '✓' : $numero }}
            </span>
            <span class="text-sm hidden sm:inline {{ $numero == $actual ? 'font-semibold text-gray-900' : 'text-gray-500' }}">
                {{ $etiqueta }}
            </span>
            @if ($numero < 3)
                <span class="w-8 h-px bg-gray-300"></span>
            @endif
        </div>
    @endforeach
</div>
