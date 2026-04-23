<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->dashboardUrl());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect($this->dashboardUrl());
        }

        return back()
            ->withErrors(['email' => 'Las credenciales no son válidas.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $adminId = $request->session()->pull('admin_impersonator_id');

        if ($adminId) {
            Auth::login(User::findOrFail($adminId));
            $request->session()->regenerate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.dashboard');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function dashboardUrl(): string
    {
        return match (Auth::user()->role) {
            'admin'   => route('admin.dashboard'),
            'maestro' => route('maestro.dashboard'),
            default   => route('alumno.mis-cursos'),
        };
    }
}
