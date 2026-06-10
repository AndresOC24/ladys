<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
