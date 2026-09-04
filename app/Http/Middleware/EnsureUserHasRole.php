<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        // Super role has access to everything
        if ($user->isSuperRole()) {
            return $next($request);
        }

        // Check if user's role matches any allowed roles
        foreach ($roles as $role) {
            if ($user->role?->value === $role) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses ke modul ini.',
                'required_roles' => $roles,
            ], 403);
        }

        abort(403, 'Akses Ditolak: Anda tidak memiliki otorisasi untuk mengakses modul ini.');
    }
}
