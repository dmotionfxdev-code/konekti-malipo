<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required','string','max:100'], 'email' => ['required','email','max:255','unique:users'], 'password' => ['required','confirmed','min:8']]);
        $user = User::create($data); Auth::login($user); $request->session()->regenerate();
        return redirect()->route('dashboard')->with('success', 'Karibu Konekti Malipo.');
    }
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required','email'], 'password' => ['required']]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) return back()->withErrors(['email' => 'Email au password si sahihi.'])->onlyInput('email');
        $request->session()->regenerate(); return redirect()->intended(route('dashboard'));
    }
    public function logout(Request $request): RedirectResponse { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('home'); }
}
