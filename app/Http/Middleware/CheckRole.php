<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /** @param  array<int, Role|string>  $roles */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $allowed = collect($roles)->map(fn (string $role) => $role);

        if (! $allowed->contains($user->role->value)) {
            abort(403, 'Accès refusé pour votre rôle.');
        }

        return $next($request);
    }
}
