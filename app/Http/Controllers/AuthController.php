<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }
    public function showForgotPassword() { return view('auth.forgot-password'); }
    public function showResetPassword(string $token) { return view('auth.reset-password', ['token' => $token]); }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        if (config('mail.default') === 'log') {
            return back()->withErrors(['email' => 'Email belum tersambung. Atur MAIL_MAILER dan kredensial SMTP di file .env terlebih dahulu.']);
        }

        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Tautan reset password dikirim jika email terdaftar.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required'], 'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);
        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
        });
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan masuk.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['username' => ['required', 'string'], 'password' => ['required', 'string']]);
        $user = User::query()->where('username', $data['username'])->orWhere('email', $data['username'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['username' => 'Username/email atau password tidak sesuai.'])->onlyInput('username');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        return redirect()->intended($user->isTeacher() ? route('teacher.dashboard') : route('student.dashboard'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'alpha_dash', 'min:3', 'max:40', 'unique:users,username'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'kelas' => ['required', 'string', 'max:40'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $user = new User();
        $user->forceFill([
            'name' => $data['name'], 'username' => $data['username'], 'email' => $data['email'],
            'kelas' => $data['kelas'], 'phone' => $data['phone'] ?? null,
            'role' => 'siswa', 'password' => Hash::make($data['password']),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('student.dashboard')->with('success', 'Akun siswa berhasil dibuat.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
