<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'petugas')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'no_hp' => 'nullable|string|max:20|unique:users,no_hp',
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'password' => Hash::make($request->password),
            'role' => 'petugas',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Petugas berhasil dibuat',
            'data' => $user,
        ], 201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'no_hp' => 'nullable|string|max:20|unique:users,no_hp,' . $id,
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Petugas berhasil diperbarui',
            'data' => $user,
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus akun super admin',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Petugas berhasil dihapus',
        ]);
    }
}
