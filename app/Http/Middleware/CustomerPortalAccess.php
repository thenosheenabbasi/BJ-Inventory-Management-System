<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerPortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->role !== User::ROLE_CUSTOMER) {
            return $next($request);
        }

        abort_unless(
            $request->routeIs('dashboard', 'reports.index', 'reports.pdf'),
            403
        );

        return $next($request);
    }
}
