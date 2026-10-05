<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Mostra o login
    public function index(){
        return view('auth.login');
    }

    // Processa a tentativa de login
    public function login(Request $request)
    {
        // Valida os campos
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        // Sucesso
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // Falha
        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registos.',
        ])->onlyInput('email');
    }
    // Termina a sessão

    public function logout(Request $request)
    {
        Auth::logout();

        // Limpa a sessão atual e gera um token (CSRF)
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
