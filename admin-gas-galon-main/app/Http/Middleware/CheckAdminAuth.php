<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login (ada token di session)
        if (!session()->has('auth_token')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Cek apakah role adalah admin
        if (session('auth_role') !== 'admin') {
            return redirect()->route('login')->with('error', 'Akses ditolak. Hanya admin yang bisa mengakses');
        }

        return $next($request);
    }
}