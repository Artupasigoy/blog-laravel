<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /**
     * Memastikan user sudah login dan akunnya aktif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika login tapi status akun inactive (0)
        if (Auth::check() && !Auth::user()->status) {
            Auth::logout(); // Paksa logout
            return redirect()->route("auth.login")->withErrors("Akun Anda saat ini tidak aktif!");
        }

        // Jika login dan aktif
        if (Auth::check() && Auth::user()->status) {
            return $next($request);
        }

        // Belum login -> lempar ke halaman login
        return redirect()->route("auth.login");
    }
}
