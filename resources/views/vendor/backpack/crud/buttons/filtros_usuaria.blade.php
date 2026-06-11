@php $roles = \App\Models\Rol::orderBy('nombre')->pluck('nombre', 'id'); @endphp

<form method="GET" action="{{ backpack_url('usuaria') }}" class="d-inline-flex gap-2 align-items-center ms-2">
    <select name="rol" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Filtrar por rol">
        <option value="">Rol: todos</option>
        @foreach ($roles as $id => $nombre)
            <option value="{{ $id }}" @selected(request('rol') == $id)>{{ ucfirst($nombre) }}</option>
        @endforeach
    </select>
    <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Filtrar por estado">
        <option value="">Estado: todos</option>
        @foreach (['pendiente', 'en_proceso', 'aprobada', 'rechazada', 'en_revision'] as $estado)
            <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
        @endforeach
    </select>
    @if (request()->hasAny(['rol', 'estado']))
        <a href="{{ backpack_url('usuaria') }}" class="btn btn-sm btn-link">Limpiar</a>
    @endif
</form>
