<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AkunController extends Controller
{
    public function cari(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['accounts' => []]);
        }

        $account = User::query()
            ->where('name', 'like', '%' . $q . '%')
            ->orWhere('email', 'like', '%' . $q . '%')
            ->limit(8)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'roleLabel' => match ($user->role) {
                    'admin' => 'Administrator',
                    'musyrif' => 'Musyrif',
                    default => 'Pengasuh',
                },
            ]);

        return response()->json(['accounts' => $account]);
    }
}