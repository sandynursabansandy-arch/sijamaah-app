<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\UserSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        UserSchema::ensurePasswordPlain();

        $users = User::latest()->get();
        return view('user.index', compact('users'));
    }

    public function store(Request $request)
    {
        UserSchema::ensurePasswordPlain();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,musyrif,pengasuh',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'password_plain' => $data['password'],
            'role' => $data['role'],
        ]);

        return back()->with('success', 'User berhasil dibuat.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Tidak bisa menghapus akun sendiri.']);
        }

        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    public function resetPassword(Request $request, User $user)
    {
        UserSchema::ensurePasswordPlain();

        $request->validate([
            'new_password' => 'required|min:6',
        ], [
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password minimal 6 karakter.',
        ]);

        $user->update([
            'password' => $request->new_password,
            'password_plain' => $request->new_password,
        ]);

        return back()->with('success', 'Password user "' . $user->name . '" berhasil direset menjadi: "' . $request->new_password . '". Informasikan ke user dan minta segera menggantinya.');
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,musyrif,pengasuh',
        ]);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Tidak bisa mengubah role akun sendiri.']);
        }

        $user->update(['role' => $request->role]);

        return back()->with('success', 'Role user "' . $user->name . '" berhasil diubah menjadi ' . $request->role . '.');
    }
}
