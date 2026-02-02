<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /**
     * Membatasi akses halaman hanya untuk Super Admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek login & Role = 3 (Super Admin)
        if (Auth::check() && Auth::user()->role == 3) {
            return $next($request);
        }
        // Jika bukan admin, return 404 (Not Found) untuk keamanan
        return abort(404);
    }
}
