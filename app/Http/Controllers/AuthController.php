<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function loginView()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $email = $request->validate([
            'email' => 'required|email',
        ])['email'];

        $password = $request->input('password', '');

        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Podane dane logowania są nieprawidłowe.',
            ])->onlyInput('email');
        }

        // Jeśli użytkownik nie ma hasła i pole hasła jest puste - zaloguj go
        if (is_null($user->password) && empty($password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect('/')->with('success', 'Zalogowano pomyślnie!');
        }

        // Jeśli użytkownik ma hasło, sprawdzamy je
        if (!is_null($user->password) && !empty($password)) {
            if (Auth::attempt(['email' => $email, 'password' => $password], $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect('/')->with('success', 'Zalogowano pomyślnie!');
            }
        }

        return back()->withErrors([
            'email' => 'Podane dane logowania są nieprawidłowe.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Wylogowano pomyślnie!');
    }
}
