<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'هذا الحساب معطل حالياً من قبل الإدارة.']);
            }

            // Check card status for university
            if ($user->role && $user->role->name === 'university') {
                if ($user->card_status === 'frozen') {
                    Auth::logout();
                    return back()->withErrors(['email' => 'تم تجميد حساب هذه الجامعة. لا يمكنك تسجيل الدخول حالياً.']);
                }
            }

            return $this->redirectUser($user);
        }

        return back()->withErrors([
            'email' => 'البيانات المدخلة غير متطابقة مع سجلاتنا.',
        ])->withInput($request->only('email', 'remember'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    protected function redirectUser($user)
    {
        if ($user->role && $user->role->name === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role && $user->role->name === 'university') {
            return redirect()->route('university.dashboard');
        }

        // Default fallback
        Auth::logout();
        return redirect()->route('login')->withErrors(['email' => 'دور المستخدم غير معروف.']);
    }
}
