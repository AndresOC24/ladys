<?php

namespace App\Http\Controllers;

use App\Http\Requests\Verificacion\Paso1Request;
use App\Http\Requests\Verificacion\Paso2Request;
use App\Http\Requests\Verificacion\Paso3Request;
use App\Jobs\ProcesarVerificacion;
use App\Models\Documento;
use App\Models\RegistroVerificacion;
use App\Models\Usuaria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VerificacionController extends Controller
{
    /**
     * Show the current verification status of the logged in user.
     */
    public function estado(Request $request): View|RedirectResponse
    {
        $usuaria = $request->user();

        if ($usuaria->estado_verificacion === 'aprobada') {
            return redirect()->route('dashboard');
        }

        $registro = $usuaria->registrosVerificacion()->latest()->first();

        return view('verificacion.estado', [
            'usuaria' => $usuaria,
            'registro' => $registro,
        ]);
    }

    /**
     * Current status as JSON, used by the polling on the processing screen.
     */
    public function estadoJson(Request $request): JsonResponse
    {
        $usuaria = $request->user();
        $registro = $this->registroActivo($usuaria) ?? $usuaria->registrosVerificacion()->latest()->first();

        return response()->json([
            'estado_usuaria' => $usuaria->estado_verificacion,
            'estado_registro' => $registro?->estado,
        ]);
    }

    /**
     * Step 1: personal data form.
     */
    public function mostrarPaso1(Request $request): View
    {
        return view('verificacion.paso1', [
            'usuaria' => $request->user(),
        ]);
    }

    /**
     * Step 1: confirm personal data and open the verification record.
     */
    public function guardarPaso1(Paso1Request $request): RedirectResponse
    {
        $usuaria = $request->user();

        $usuaria->update($request->validated());

        if (! $this->registroActivo($usuaria)) {
            $usuaria->registrosVerificacion()->create([
                'fecha_inicio' => now(),
                'estado' => 'pendiente',
            ]);
        }

        return redirect()->route('verificacion.paso2');
    }

    /**
     * Step 2: identity card upload form.
     */
    public function mostrarPaso2(Request $request): View|RedirectResponse
    {
        $registro = $this->registroActivo($request->user());

        if (! $registro) {
            return redirect()->route('verificacion.paso1');
        }

        return view('verificacion.paso2', [
            'registro' => $registro,
        ]);
    }

    /**
     * Step 2: store both sides of the identity card.
     */
    public function guardarPaso2(Paso2Request $request): RedirectResponse
    {
        $registro = $this->registroActivo($request->user());

        if (! $registro) {
            return redirect()->route('verificacion.paso1');
        }

        $this->guardarDocumento($registro, 'anverso', $request->file('anverso'), 'cedulas');
        $this->guardarDocumento($registro, 'reverso', $request->file('reverso'), 'cedulas');

        return redirect()->route('verificacion.paso3');
    }

    /**
     * Step 3: live face capture form.
     */
    public function mostrarPaso3(Request $request): View|RedirectResponse
    {
        $registro = $this->registroActivo($request->user());

        if (! $registro) {
            return redirect()->route('verificacion.paso1');
        }

        if ($registro->documentos()->whereIn('tipo', ['anverso', 'reverso'])->count() < 2) {
            return redirect()->route('verificacion.paso2');
        }

        return view('verificacion.paso3', [
            'registro' => $registro,
        ]);
    }

    /**
     * Step 3: store the selfie and queue the verification.
     */
    public function guardarPaso3(Paso3Request $request): RedirectResponse
    {
        $usuaria = $request->user();
        $registro = $this->registroActivo($usuaria);

        if (! $registro) {
            return redirect()->route('verificacion.paso1');
        }

        if ($registro->documentos()->whereIn('tipo', ['anverso', 'reverso'])->count() < 2) {
            return redirect()->route('verificacion.paso2');
        }

        $this->guardarDocumento($registro, 'selfie', $request->file('selfie'), 'selfies');

        $registro->update(['estado' => 'en_proceso']);
        $usuaria->update(['estado_verificacion' => 'en_proceso']);

        ProcesarVerificacion::dispatch($registro);

        return redirect()->route('verificacion.procesando');
    }

    /**
     * Waiting screen while the verification runs.
     */
    public function procesando(Request $request): View|RedirectResponse
    {
        $usuaria = $request->user();

        if ($usuaria->estado_verificacion !== 'en_proceso') {
            return redirect()->route('verificacion.resultado');
        }

        return view('verificacion.procesando');
    }

    /**
     * Final screen with the verification outcome.
     */
    public function mostrarResultado(Request $request): View|RedirectResponse
    {
        $usuaria = $request->user();
        $registro = $usuaria->registrosVerificacion()
            ->with(['resultadosValidacion', 'revisionAdministrativa'])
            ->latest()
            ->first();

        if (! $registro) {
            return redirect()->route('verificacion.paso1');
        }

        if ($usuaria->estado_verificacion === 'en_proceso') {
            return redirect()->route('verificacion.procesando');
        }

        $motivos = $registro->resultadosValidacion
            ->where('resultado', '!=', 'aprobado')
            ->map(fn ($r) => $r->detalles['motivo'] ?? "Validación {$r->tipo}: {$r->resultado}")
            ->values();

        if ($registro->revisionAdministrativa?->observaciones) {
            $motivos->push($registro->revisionAdministrativa->observaciones);
        }

        return view('verificacion.resultado', [
            'usuaria' => $usuaria,
            'registro' => $registro,
            'motivos' => $motivos,
        ]);
    }

    /**
     * The verification record still open for this user, if any.
     */
    protected function registroActivo(Usuaria $usuaria): ?RegistroVerificacion
    {
        return $usuaria->registrosVerificacion()
            ->whereNotIn('estado', ['aprobada', 'rechazada'])
            ->latest()
            ->first();
    }

    /**
     * Store an uploaded image on the private disk and persist its record.
     * Re-uploads of the same type replace the previous file.
     */
    protected function guardarDocumento(RegistroVerificacion $registro, string $tipo, UploadedFile $archivo, string $carpeta): Documento
    {
        $nombre = sprintf('%d_%s_%d.%s', $registro->id, $tipo, now()->getTimestamp(), $archivo->getClientOriginalExtension());
        $ruta = $archivo->storeAs($carpeta, $nombre, 'private');

        $existente = $registro->documentos()->where('tipo', $tipo)->first();

        if ($existente) {
            Storage::disk('private')->delete($existente->ruta_archivo);
            $existente->update([
                'ruta_archivo' => $ruta,
                'hash_archivo' => hash_file('sha256', $archivo->getRealPath()),
            ]);

            return $existente;
        }

        return $registro->documentos()->create([
            'tipo' => $tipo,
            'ruta_archivo' => $ruta,
            'hash_archivo' => hash_file('sha256', $archivo->getRealPath()),
        ]);
    }
}
