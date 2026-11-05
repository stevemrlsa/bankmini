<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function indexUsers()
    {
        $users = User::all();

        return response()->json([
            'success' => true,
            'message' => 'Daftar user berhasil diambil',
            'data' => $users
        ], 200);
    }

    public function storeUser(Request $request) 
    {
        $validated = $request->validate([
            'officer_name'    => 'required|string|max:255',
            'username'        => 'required|string|max:255|unique:users,username',
            'email'           => 'required|email|unique:users,email',
            'whatsapp_number' => 'required|string|max:20',
            'password'        => 'required|string|min:6',
            'role'            => 'required|in:Administrator,Teller',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Akun useer internal berhasil dibuat',
            'data' => $user
        ], 200);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'officer_name'    => 'sometimes|required|string|max:255',
            'username'        => 'sometimes|required|string|max:255|unique:users,username,' . $id,
            'email'           => 'sometimes|required|email|unique:users,email,' . $id,
            'whatsapp_number' => 'sometimes|required|string|max:20',
            'password'        => 'sometimes|required|string|min:6',
            'role'            => 'sometimes|required|in:Administrator,Teller',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diupdate',
            'data' => $user
        ], 200);
    }

    public function destroyUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
            'success' => false,
            'message' => 'Pengguna tidak ditemukan',
            'data' => null
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun pengguna berhasil dihapus',
            'data' => null
        ], 200);
    }

    public function auditJournals()
    {
        $journals = DB::table('accounting_journals')
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data audit jurnal akuntansi berhasil diambil',
            'data' => $journals
        ], 200);
    }
}
