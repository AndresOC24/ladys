{{-- This file is used for menu items by any Backpack v7 theme --}}
@php
    $casosEnRevision = \App\Models\RegistroVerificacion::where('estado', 'en_revision')->count();
@endphp

<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('registro-verificacion') }}">
        <i class="la la-id-card nav-icon"></i> Verificaciones
        @if ($casosEnRevision > 0)
            <span class="badge bg-purple text-white ms-auto">{{ $casosEnRevision }}</span>
        @endif
    </a>
</li>

<li class="nav-item"><a class="nav-link" href="{{ backpack_url('usuaria') }}"><i class="la la-users nav-icon"></i> Usuarias</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('parametro-control') }}"><i class="la la-sliders-h nav-icon"></i> Parámetros de control</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('rol') }}"><i class="la la-user-tag nav-icon"></i> Roles</a></li>
