<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador de inicio y cierre de sesión.
 */
class AuthController extends Controller
{
    // Muestra el formulario de ingreso (GET /login)
    public function showLogin()
    {
        // Si ya tiene sesión, va directo al panel
        if (Auth::check()) {
            return redirect()->route('panel');
        }

        return view('auth.login');
    }

    // Procesa el formulario (POST /login)
    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ingresar un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // Solo puede entrar un usuario activo (los bloqueados fallan con el mismo mensaje)
        $credenciales['estado'] = 'activo';

        if (Auth::attempt($credenciales)) {
            // Regenera la sesión para impedir el robo de sesión (fijación)
            $request->session()->regenerate();

            return redirect()->intended(route('panel'));
        }

        return back()
            ->withErrors(['email' => 'Correo o contraseña incorrectos.'])
            ->onlyInput('email');
    }

    // Cierra la sesión (POST /logout)
    public function logout(Request $request)
    {
        Auth::logout();

        // Borra los datos de la sesión y el token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
