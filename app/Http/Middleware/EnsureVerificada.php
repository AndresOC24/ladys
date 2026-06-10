<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerificada
{
    /**
     * Routes exempt from the verification requirement.
     *
     * @var array<int, string>
     */
    protected array $except = [
        'verificacion/*',
        'verificacion',
        'logout',
    ];

    /**
     * Redirect to the verification flow unless the user is already approved.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuaria = $request->user();

        if ($usuaria && $usuaria->estado_verificacion !== 'aprobada' && ! $this->inExceptArray($request)) {
            return redirect()->route('verificacion.estado');
        }

        return $next($request);
    }

    protected function inExceptArray(Request $request): bool
    {
        foreach ($this->except as $except) {
            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
