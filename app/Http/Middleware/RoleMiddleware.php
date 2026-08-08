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
        if (!$user->role || $user->role->name !== $role) {
            if ($user->role && $user->role->name === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role && $user->role->name === 'university') {
                return redirect()->route('university.dashboard');
            }
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'غير مصرح لك بالوصول لهذه الصفحة.']);
        }

        return $next($request);
    }
}
