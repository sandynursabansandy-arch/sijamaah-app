<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('presensi.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, false)) {
            $request->session()->regenerate();
            return redirect()->route('presensi.index');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('presensi.index');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar. Silakan masuk atau gunakan email lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        \App\Support\UserSchema::ensurePasswordPlain();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'password_plain' => $data['password'],
            'role' => 'pengasuh',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('presensi.index')->with('success', 'Akun berhasil dibuat. Selamat datang, ' . $user->name . '!');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        \App\Support\UserSchema::ensurePasswordPlain();

        $user->update([
            'password' => $request->password,
            'password_plain' => $request->password,
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function showProfile()
    {
        $user = Auth::user();
        $santri = \App\Models\Santri::where('nama', $user->name)->first();

        return view('auth.profile', compact('user', 'santri'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama wajib diisi.',
        ]);

        $oldName = $user->name;
        $user->update(['name' => $request->name]);

        if ($oldName !== $request->name) {
            $santri = \App\Models\Santri::where('nama', $oldName)->first();
            if ($santri) {
                $santri->update(['nama' => $request->name]);
            }
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updateFontSize(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'font_size' => 'required|in:kecil,sedang,besar',
        ]);

        $user->update(['font_size' => $request->font_size]);

        return back()->with('success', 'Ukuran font berhasil diubah.');
    }
}
