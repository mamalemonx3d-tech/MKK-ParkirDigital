<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda belum login.',
            ], 401);
        }

        $userRoles = [$user->role];
        if ($user->role === 'super_admin') {
            $userRoles[] = 'admin';
        }

        if (empty(array_intersect($userRoles, $roles))) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin.',
            ], 403);
        }

        return $next($request);
    }
}
