<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = $user->role ? $user->role->name : null;

        // Admin has super-user access to preview and manage both portals
        if ($userRole === 'admin') {
            return $next($request);
        }

        if ($userRole !== $role) {
            if ($userRole === 'university') {
                return redirect()->route('university.dashboard');
            }
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'غير مصرح لك بالوصول لهذه الصفحة.']);
        }

        return $next($request);
    }
}
