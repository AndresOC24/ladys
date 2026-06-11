@extends(backpack_view('blank'))

@php
    use App\Models\RegistroVerificacion;
    use App\Models\Usuaria;

    $pendientes = RegistroVerificacion::with('usuaria')
        ->where('estado', 'en_revision')
        ->orderBy('updated_at')
        ->get();

    $stats = [
        ['titulo' => 'Casos en revisión', 'valor' => $pendientes->count(), 'clase' => 'text-purple'],
        ['titulo' => 'Aprobadas hoy', 'valor' => RegistroVerificacion::where('estado', 'aprobada')->whereDate('fecha_resolucion', today())->count(), 'clase' => 'text-green'],
        ['titulo' => 'Usuarias registradas', 'valor' => Usuaria::count(), 'clase' => 'text-pink'],
    ];
@endphp

@section('header')
    <div class="container-fluid">
        <h2 class="mb-0">Panel de administración</h2>
        <p class="text-muted mb-0">Validación de identidad — Lady's On Go</p>
    </div>
@endsection

@section('content')
    <div class="row g-3 mb-3">
        @foreach ($stats as $stat)
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">{{ $stat['titulo'] }}</div>
                        <div class="h1 mb-0 {{ $stat['clase'] }}">{{ $stat['valor'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Bandeja de revisión administrativa</h3>
            <a href="{{ backpack_url('registro-verificacion') }}" class="btn btn-sm btn-link">Ver todos los registros</a>
        </div>
        <div class="card-body p-0">
            @if ($pendientes->isEmpty())
                <div class="p-4 text-muted">No hay casos esperando revisión. 🎉</div>
            @else
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuaria</th>
                            <th>Rol</th>
                            <th>Esperando desde</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendientes as $registro)
                            <tr>
                                <td>{{ $registro->id }}</td>
                                <td>{{ $registro->usuaria?->name }} <span class="text-muted">({{ $registro->usuaria?->email }})</span></td>
                                <td>{{ ucfirst($registro->usuaria?->rol?->nombre ?? '—') }}</td>
                                <td>
                                    {{ $registro->updated_at->diffForHumans() }}
                                    @if ($registro->updated_at->diffInHours() >= 48)
                                        <span class="badge bg-red-lt text-red">+48h</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ backpack_url('revision/'.$registro->id) }}" class="btn btn-sm btn-primary">
                                        <i class="la la-search"></i> Revisar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
