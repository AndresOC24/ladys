<?php

namespace App\Jobs;

use App\Models\ParametroControl;
use App\Models\RegistroVerificacion;
use App\Services\EvaluadorVerificacion;
use App\Services\ServicioIA;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcesarVerificacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Reintentos ante caídas del servicio de IA. */
    public int $tries = 3;

    /** @var array<int, int> Espera (segundos) entre reintentos. */
    public array $backoff = [10, 30];

    public function __construct(
        public RegistroVerificacion $registro,
    ) {}

    public function handle(ServicioIA $ia, EvaluadorVerificacion $evaluador): void
    {
        $rutaSelfie = $this->rutaDocumento('selfie');
        $rutaAnverso = $this->rutaDocumento('anverso');

        if (! $rutaSelfie || ! $rutaAnverso) {
            Log::warning('ProcesarVerificacion: documentos incompletos', ['registro' => $this->registro->id]);
            $this->resolver('en_revision');

            return;
        }

        $umbralLiveness = (float) ParametroControl::valorDe('umbral_liveness', '0.85');
        $umbralFacial = (float) ParametroControl::valorDe('umbral_facial_aprobado', '0.68');

        // Secuencia: liveness → facial → OCR. Se ejecutan las tres aunque
        // alguna falle, para que la revisión administrativa vea el cuadro completo.
        $liveness = $ia->detectarLiveness($rutaSelfie, $umbralLiveness);
        $facial = $ia->verificarRostro($rutaSelfie, $rutaAnverso, $umbralFacial);
        $ocr = $ia->extraerDatosCedula($rutaAnverso, $this->rutaDocumento('reverso'));

        $decision = $evaluador->evaluar($liveness, $facial, $ocr);

        $this->guardarResultados($decision['componentes']);
        $this->guardarDatosDocumento($ocr);
        $this->guardarRostroCedula($ia, $rutaAnverso);
        $this->resolver($decision['veredicto']);

        Log::info('Verificación procesada', [
            'registro' => $this->registro->id,
            'veredicto' => $decision['veredicto'],
        ]);
    }

    /**
     * Tras agotar los reintentos (servicio de IA caído u otro fallo no
     * recuperable), el caso pasa a revisión humana y se alerta al equipo.
     */
    public function failed(Throwable $excepcion): void
    {
        Log::critical('ProcesarVerificacion agotó sus reintentos; caso derivado a revisión administrativa', [
            'registro' => $this->registro->id,
            'usuaria' => $this->registro->usuaria_id,
            'error' => $excepcion->getMessage(),
        ]);

        $this->resolver('en_revision', resuelto: false);
    }

    protected function rutaDocumento(string $tipo): ?string
    {
        $documento = $this->registro->documentos()->where('tipo', $tipo)->first();

        if (! $documento || ! Storage::disk('private')->exists($documento->ruta_archivo)) {
            return null;
        }

        return Storage::disk('private')->path($documento->ruta_archivo);
    }

    /** @param  array<string, array>  $componentes */
    protected function guardarResultados(array $componentes): void
    {
        // Un reintento del flujo reemplaza los resultados anteriores del registro.
        $this->registro->resultadosValidacion()->delete();

        foreach ($componentes as $tipo => $componente) {
            $this->registro->resultadosValidacion()->create([
                'tipo' => $tipo,
                'puntaje' => $componente['puntaje'],
                'resultado' => $componente['resultado'],
                'detalles' => $componente['detalles'],
            ]);
        }
    }

    protected function guardarDatosDocumento(array $ocr): void
    {
        if (! $ocr['exito']) {
            return;
        }

        $datos = $ocr['datos'];

        $this->registro->datosDocumento()->updateOrCreate(
            ['registro_verificacion_id' => $this->registro->id],
            [
                'numero_cedula' => $datos['numero_cedula'] ?? null,
                'serie' => $datos['serie'] ?? null,
                'seccion' => $datos['seccion'] ?? null,
                'nombre_completo' => $datos['nombre_completo'] ?? null,
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
                'fecha_emision' => $datos['fecha_emision'] ?? null,
                'fecha_vencimiento' => $datos['fecha_vencimiento'] ?? null,
                'lugar_nacimiento' => $datos['lugar_nacimiento'] ?? null,
                'domicilio' => $datos['domicilio'] ?? null,
                'ocupacion' => $datos['ocupacion'] ?? null,
                'estado_civil' => $datos['estado_civil'] ?? null,
            ]
        );
    }

    /**
     * Guarda el recorte del rostro impreso en la cédula como evidencia
     * adicional para la revisión administrativa. Si el servicio no puede
     * extraerlo no bloquea el flujo: la verificación 1:1 ya lo comparó.
     */
    protected function guardarRostroCedula(ServicioIA $ia, string $rutaAnverso): void
    {
        try {
            $recorte = $ia->extraerRostroCedula($rutaAnverso);
        } catch (Throwable $excepcion) {
            Log::warning('No se pudo extraer el rostro de la cédula', [
                'registro' => $this->registro->id,
                'error' => $excepcion->getMessage(),
            ]);

            return;
        }

        if ($recorte === null) {
            return;
        }

        $ruta = sprintf('rostros/%d_rostro_cedula_%d.jpg', $this->registro->id, now()->getTimestamp());
        Storage::disk('private')->put($ruta, $recorte);

        $existente = $this->registro->documentos()->where('tipo', 'rostro_cedula')->first();

        if ($existente) {
            Storage::disk('private')->delete($existente->ruta_archivo);
            $existente->update(['ruta_archivo' => $ruta, 'hash_archivo' => hash('sha256', $recorte)]);

            return;
        }

        $this->registro->documentos()->create([
            'tipo' => 'rostro_cedula',
            'ruta_archivo' => $ruta,
            'hash_archivo' => hash('sha256', $recorte),
        ]);
    }

    protected function resolver(string $estado, bool $resuelto = true): void
    {
        $this->registro->update([
            'estado' => $estado,
            'fecha_resolucion' => $resuelto && in_array($estado, ['aprobada', 'rechazada']) ? now() : null,
        ]);

        $this->registro->usuaria->update(['estado_verificacion' => $estado]);
    }
}
