<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class superAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifiez si l'utilisateur est connecté et est un super administrateur
        
        if (Auth::user() &&  Auth::user()->superAdmin == 1) {
            return $next($request);
       }

       return response()->json(['error' => 'Cet Utilisateur nest pas un super Administrateur.'], 401);

    }
}
