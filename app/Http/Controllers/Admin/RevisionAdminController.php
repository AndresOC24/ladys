<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistroVerificacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RevisionAdminController extends Controller
{
    /**
     * Detailed review page for one verification case.
     */
    public function show(RegistroVerificacion $registro): View
    {
        $registro->load([
            'usuaria.rol',
            'documentos',
            'datosDocumento',
            'resultadosValidacion',
            'revisionAdministrativa.administrador',
        ]);

        return view('admin.revision', [
            'registro' => $registro,
            'documentos' => $registro->documentos->keyBy('tipo'),
            'resultados' => $registro->resultadosValidacion->keyBy('tipo'),
            'title' => 'Revisión del registro #'.$registro->id,
        ]);
    }

    /**
     * Persist the administrative decision over a case.
     */
    public function decidir(Request $request, RegistroVerificacion $registro): RedirectResponse
    {
        $datos = $request->validate([
            'decision' => ['required', Rule::in(['aprobada', 'rechazada', 'solicitar_reenvio'])],
            'observaciones' => [
                Rule::requiredIf(fn () => in_array($request->decision, ['rechazada', 'solicitar_reenvio'])),
                'nullable', 'string', 'max:1000',
            ],
        ], [
            'observaciones.required' => 'Debes indicar el motivo cuando rechazas o solicitas reenvío.',
        ]);

        $registro->revisionAdministrativa()->updateOrCreate(
            ['registro_verificacion_id' => $registro->id],
            [
                'administrador_id' => backpack_user()->id,
                'decision' => $datos['decision'],
                'observaciones' => $datos['observaciones'] ?? null,
                'fecha_revision' => now(),
            ]
        );

        // solicitar_reenvio deja a la usuaria como rechazada para que el flujo
        // le ofrezca "Intentar nuevamente" mostrando las observaciones.
        $estadoFinal = $datos['decision'] === 'aprobada' ? 'aprobada' : 'rechazada';

        $registro->update([
            'estado' => $estadoFinal,
            'fecha_resolucion' => now(),
        ]);

        $registro->usuaria->update(['estado_verificacion' => $estadoFinal]);

        \Alert::success('Decisión registrada: '.str_replace('_', ' ', $datos['decision']))->flash();

        return redirect(backpack_url('dashboard'));
    }
}
