<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check() || !in_array(Auth::user()->role, $roles, true)) {
            $required = implode(', ', array_map('ucfirst', $roles));
            abort(403, "Akses ditolak. Halaman atau aksi ini hanya dapat diakses oleh peran: {$required}.");
        }

        return $next($request);
    }
}
