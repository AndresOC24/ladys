@props(['actual' => 1])

<div class="flex items-center justify-center gap-2 mb-8" role="list" aria-label="Progreso de verificación">
    @foreach ([1 => 'Datos personales', 2 => 'Cédula de identidad', 3 => 'Captura facial'] as $numero => $etiqueta)
        <div class="flex items-center gap-2" role="listitem" @if($numero == $actual) aria-current="step" @endif>
            <span class="flex items-center justify-center w-9 h-9 rounded-full text-sm font-bold transition duration-200
                {{ $numero < $actual ? 'bg-green-500 text-white' : ($numero == $actual ? 'bg-gradient-to-br from-primary-500 to-accent-500 text-white shadow-sm shadow-primary-200' : 'bg-white border border-primary-200 text-slate-400') }}">
                @if ($numero < $actual)
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                @else
                    {{ $numero }}
                @endif
            </span>
            <span class="text-sm hidden sm:inline {{ $numero == $actual ? 'font-semibold text-slate-900' : 'text-slate-500' }}">
                {{ $etiqueta }}
            </span>
            @if ($numero < 3)
                <span class="w-8 h-px {{ $numero < $actual ? 'bg-green-400' : 'bg-primary-200' }}"></span>
            @endif
        </div>
    @endforeach
</div>
