<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWeb
{
    private array $roleRoutes = [
        'admin' => ['System Administrator'],
        'staff' => ['Farmmantra Staff'],
        'finance' => ['Finance Department'],
        'franchise' => ['Franchise Partner'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('web.login');
        }

        $routeName = $request->route()->getName() ?? '';
        $userRole = auth()->user()->role?->name ?? '';

        foreach ($this->roleRoutes as $prefix => $roles) {
            if (str_starts_with($routeName, "web.{$prefix}")) {
                if (!in_array($userRole, $roles)) {
                    abort(403, 'Unauthorized access.');
                }
                break;
            }
        }

        return $next($request);
    }
}
