<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DocumentoAdminController extends Controller
{
    /**
     * Stream a private document image to the admin panel.
     *
     * El disco "private" nunca expone URLs públicas; esta es la única vía
     * de lectura y está detrás del middleware admin de Backpack.
     */
    public function show(Documento $documento): Response
    {
        abort_unless(Storage::disk('private')->exists($documento->ruta_archivo), 404);

        return Storage::disk('private')->response($documento->ruta_archivo);
    }
}
