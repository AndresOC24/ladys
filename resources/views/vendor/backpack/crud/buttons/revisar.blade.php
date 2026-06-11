@if ($crud->model instanceof \App\Models\RegistroVerificacion || $entry instanceof \App\Models\RegistroVerificacion)
    <a href="{{ backpack_url('revision/'.$entry->getKey()) }}" class="btn btn-sm btn-primary">
        <i class="la la-search"></i> Revisar
    </a>
@endif
