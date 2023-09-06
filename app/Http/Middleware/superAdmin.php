<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    
    public function handle(Request $request, Closure $next)
    {
        // Vérifiez si l'utilisateur est connecté et est un super administrateur

        if (Auth::user() &&  Auth::user()->type == "superAdmin") {
            
            return $next($request); // L'utilisateur est un super administrateur, laissez-le accéder à la route
       }

        return response()->json(['error' => 'Cet Utilisateur nest pas un super Administrateur.'], 401);
        
    }
}
