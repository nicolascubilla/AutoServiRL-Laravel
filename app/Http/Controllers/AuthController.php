<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('usuario', 'contrasena');

        if (Auth::attempt(['usuario' => $credentials['usuario'], 'password' => $credentials['contrasena']])) {
            $user = Auth::user();
            Session::put('user_id', $user->id_usuario);
            Session::put('usuario', $user->usuario);
            Session::put('nombre', $user->nombre_completo);

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('usuario'))
            ->withErrors(['login' => 'Usuario o contraseña incorrectos']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
