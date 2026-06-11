@extends(backpack_view('blank'))

@php
    $badge = fn (string $resultado) => match ($resultado) {
        'aprobado' => 'bg-green-lt text-green',
        'dudoso' => 'bg-purple-lt text-purple',
        'rechazado' => 'bg-red-lt text-red',
        default => 'bg-secondary-lt',
    };
    $usuaria = $registro->usuaria;
    $datos = $registro->datosDocumento;
    $facial = $resultados->get('facial');
@endphp

@section('header')
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">Revisión del registro #{{ $registro->id }}</h2>
            <p class="text-muted mb-0">
                {{ $usuaria->name }} ({{ $usuaria->email }}) — {{ ucfirst($usuaria->rol?->nombre ?? '—') }}
                · Estado actual: <strong>{{ str_replace('_', ' ', $registro->estado) }}</strong>
            </p>
        </div>
        <a href="{{ backpack_url('registro-verificacion') }}" class="btn btn-link">&larr; Volver al listado</a>
    </div>
@endsection

@section('content')
<div class="row g-3">

    {{-- Imágenes en grande --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Evidencias</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach (['selfie' => 'Captura en vivo', 'anverso' => 'Cédula — anverso', 'reverso' => 'Cédula — reverso'] as $tipo => $titulo)
                        <div class="col-md-4">
                            <div class="text-muted small mb-1">{{ $titulo }}</div>
                            @if ($documentos->has($tipo))
                                <a href="{{ backpack_url('documento/'.$documentos[$tipo]->id) }}" target="_blank" title="Abrir en tamaño completo">
                                    <img src="{{ backpack_url('documento/'.$documentos[$tipo]->id) }}"
                                        alt="{{ $titulo }}" class="img-fluid rounded border" style="max-height: 320px; object-fit: contain; width: 100%; background: #f8fafc;">
                                </a>
                                <div class="text-muted small mt-1" style="word-break: break-all;">SHA256: {{ $documentos[$tipo]->hash_archivo }}</div>
                            @else
                                <div class="alert alert-warning mb-0">No cargado</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Puntajes de los 3 módulos --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Resultados de la validación automática</h3></div>
            <div class="card-body">
                @if ($resultados->isEmpty())
                    <div class="alert alert-info mb-0">Este registro aún no tiene resultados automáticos (la verificación no se ejecutó o falló antes de completarse).</div>
                @else
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Módulo</th><th>Puntaje</th><th>Resultado</th><th>Detalle</th></tr></thead>
                        <tbody>
                            @foreach (['liveness' => 'Prueba de vida', 'facial' => 'Comparación facial', 'ocr' => 'OCR documental'] as $tipo => $nombre)
                                @php $r = $resultados->get($tipo); @endphp
                                <tr>
                                    <td>{{ $nombre }}</td>
                                    <td>{{ $r?->puntaje ?? '—' }}</td>
                                    <td>@if($r)<span class="badge {{ $badge($r->resultado) }}">{{ $r->resultado }}</span>@else — @endif</td>
                                    <td class="text-muted small">{{ $r?->detalles['motivo'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if ($facial)
                        <div class="text-muted small">
                            Distancia facial exacta: <strong>{{ $facial->puntaje }}</strong>
                            · Umbral de aprobación: <strong>{{ $facial->detalles['umbral_aprobado'] ?? '—' }}</strong>
                            · Umbral dudoso: <strong>{{ $facial->detalles['umbral_dudoso'] ?? '—' }}</strong>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Datos OCR --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Datos extraídos del documento (OCR)</h3></div>
            <div class="card-body">
                @if ($datos)
                    <dl class="row mb-0">
                        <dt class="col-5">Número de cédula</dt><dd class="col-7">{{ $datos->numero_cedula ?? '—' }}</dd>
                        <dt class="col-5">Nombre completo</dt><dd class="col-7">{{ $datos->nombre_completo ?? '—' }}</dd>
                        <dt class="col-5">Fecha de nacimiento</dt><dd class="col-7">{{ $datos->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</dd>
                        <dt class="col-5">Fecha de emisión</dt><dd class="col-7">{{ $datos->fecha_emision?->format('d/m/Y') ?? '—' }}</dd>
                        <dt class="col-5">Fecha de expiración</dt><dd class="col-7">{{ $datos->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</dd>
                    </dl>
                    <hr>
                    <div class="text-muted small">
                        Declarado por la usuaria: <strong>{{ $usuaria->name }}</strong>, nacimiento
                        <strong>{{ $usuaria->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</strong>. Contrasta ambos antes de decidir.
                    </div>
                @else
                    <div class="alert alert-warning mb-0">El OCR no devolvió datos para este registro.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Decisión --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Decisión administrativa</h3></div>
            <div class="card-body">
                @if ($registro->revisionAdministrativa)
                    <div class="alert alert-info">
                        Este caso ya fue revisado por <strong>{{ $registro->revisionAdministrativa->administrador?->name ?? '—' }}</strong>
                        el {{ $registro->revisionAdministrativa->fecha_revision?->format('d/m/Y H:i') }}:
                        decisión <strong>{{ str_replace('_', ' ', $registro->revisionAdministrativa->decision) }}</strong>.
                        @if ($registro->revisionAdministrativa->observaciones)
                            <br>Observaciones: {{ $registro->revisionAdministrativa->observaciones }}
                        @endif
                        <br><span class="text-muted small">Puedes registrar una nueva decisión; reemplazará a la anterior.</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ backpack_url('revision/'.$registro->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="observaciones">Observaciones <span class="text-muted">(obligatorias al rechazar o solicitar reenvío)</span></label>
                        <textarea name="observaciones" id="observaciones" rows="3" class="form-control"
                            placeholder="Ej.: La fotografía del anverso está borrosa; vuelve a capturarla con buena luz.">{{ old('observaciones') }}</textarea>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="decision" value="aprobada" class="btn btn-success"
                            onclick="return confirm('¿Aprobar la identidad de {{ $usuaria->name }}?')">
                            <i class="la la-check"></i> Aprobar
                        </button>
                        <button type="submit" name="decision" value="rechazada" class="btn btn-danger"
                            onclick="return confirm('¿Rechazar definitivamente este caso?')">
                            <i class="la la-times"></i> Rechazar
                        </button>
                        <button type="submit" name="decision" value="solicitar_reenvio" class="btn btn-outline-secondary">
                            <i class="la la-redo"></i> Solicitar reenvío
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
