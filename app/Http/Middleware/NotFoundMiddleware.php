<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NotFoundMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (strpos($request->url(), '/api') === 0 && !$request->route()) {
            print_r("esto se ejecuta ok");
            exit;
            return response()->json(['error' => 'La ruta de la API solicitada no existe'], 404);
        }
    
        return $next($request);
    }
}
