<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    protected function routeByRole(?User $user = null): string
    {
        $role = $user?->role ?? Auth::user()?->role ?? 'user';

        return match ($role) {
            'admin' => route('admin.dashboard'),
            'atasan' => route('atasan.dashboard'),
            default => route('user.dashboard'),
        };
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect($this->routeByRole(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended($this->routeByRole(Auth::user()));
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'unit_kerja' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'unit_kerja' => $request->unit_kerja ?? 'Unit Kerja Baru',
        ]);

        Auth::login($user);

        return redirect($this->routeByRole($user))->with('success', 'Akun berhasil dibuat! Selamat datang di Portal Maturity Level K3 PLN.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}