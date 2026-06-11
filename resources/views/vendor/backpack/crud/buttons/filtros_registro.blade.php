<form method="GET" action="{{ backpack_url('registro-verificacion') }}" class="d-inline-flex gap-2 align-items-center ms-2 flex-wrap">
    <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Filtrar por estado">
        <option value="">Estado: todos</option>
        @foreach (['pendiente', 'en_proceso', 'aprobada', 'rechazada', 'en_revision'] as $estado)
            <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
        @endforeach
    </select>
    <label class="text-muted small mb-0" for="filtro-desde">Inicio desde</label>
    <input id="filtro-desde" type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm" onchange="this.form.submit()">
    <label class="text-muted small mb-0" for="filtro-hasta">hasta</label>
    <input id="filtro-hasta" type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm" onchange="this.form.submit()">
    @if (request()->hasAny(['estado', 'desde', 'hasta']))
        <a href="{{ backpack_url('registro-verificacion') }}" class="btn btn-sm btn-link">Limpiar</a>
    @endif
</form>
