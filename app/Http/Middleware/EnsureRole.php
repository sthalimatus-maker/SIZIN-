<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $allowedRoles = $role === 'guru' ? ['guru', 'teacher'] : ['siswa', 'student'];
        abort_unless($request->user() && in_array($request->user()->role, $allowedRoles, true), 403);
        return $next($request);
    }
}
