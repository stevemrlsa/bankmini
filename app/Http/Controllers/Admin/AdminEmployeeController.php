<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Officer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminEmployeeController extends Controller
{
    /**
     * Display a listing of bank employees (Administrators and Tellers).
     * Handles search queries by Name, Username, or Role.
     */
    public function index(Request $request)
    {
        // 1. Fetch bank staff only (exclude student savers)
        $query = User::whereIn('role', ['Administrator', 'Teller', 'Supervisor']);

        // 2. Search by Name, Username, or Role
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('officer_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('username', 'like', '%' . $searchTerm . '%')
                  ->orWhere('role', 'like', '%' . $searchTerm . '%');
            });

            // Prioritize closest matches at the top of the table
            $query->orderByRaw("CASE 
                WHEN officer_name LIKE ? THEN 1 
                WHEN username LIKE ? THEN 2 
                WHEN role LIKE ? THEN 3 
                ELSE 4 END", 
                ["%{$searchTerm}%", "%{$searchTerm}%", "%{$searchTerm}%"]
            );
        }

        // 3. Default sort by created_at descending, fallback alphabetical
        $users = $query->orderBy('created_at', 'desc')
            ->orderBy('officer_name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.employee-management', compact('users'));
    }

    /**
     * Store a newly created employee in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'officer_name'    => 'required|string|max:255',
            'username'        => 'required|string|unique:users,username',
            'email'           => 'required|email|unique:users,email',
            'whatsapp_number' => 'required|string|max:20',
            'password'        => 'required|string|min:6',
            'role'            => 'required|in:Administrator,Teller,Supervisor',
            'nip'             => 'nullable|string|max:50',
            'address'         => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status']   = 'Aktif';

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'officer_name'    => $validated['officer_name'],
                'username'        => $validated['username'],
                'email'           => $validated['email'],
                'whatsapp_number' => $validated['whatsapp_number'],
                'password'        => $validated['password'],
                'role'            => $validated['role'],
                'status'          => $validated['status'],
            ]);

            Officer::create([
                'user_id'         => $user->id,
                'officer_name'    => $validated['officer_name'],
                'whatsapp_number' => $validated['whatsapp_number'],
                'status'          => $validated['status'],
                'nip'             => $request->nip ?? null,
                'address'         => $request->address ?? null,
            ]);
        });

        \App\Models\AuditLog::record(
            'TAMBAH_PEGAWAI',
            'Manajemen Pegawai',
            "Menambahkan pegawai baru: {$validated['officer_name']} ({$validated['role']}, @{$validated['username']})",
            Auth::id(),
            Auth::user()?->officer_name ?? Auth::user()?->username ?? 'Administrator'
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pegawai baru berhasil ditambahkan.');
    }

    /**
     * Update an existing employee in database.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'officer_name'    => 'required|string|max:255',
            'username'        => 'required|string|unique:users,username,' . $id,
            'email'           => 'required|email|unique:users,email,' . $id,
            'whatsapp_number' => 'required|string|max:20',
            'role'            => 'required|in:Administrator,Teller,Supervisor',
            'password'        => 'nullable|string|min:6'
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($user->officer) {
            $user->officer->update([
                'officer_name'    => $validated['officer_name'],
                'whatsapp_number' => $validated['whatsapp_number'],
            ]);
        }

        \App\Models\AuditLog::record(
            'UPDATE_PEGAWAI',
            'Manajemen Pegawai',
            "Memperbarui data pegawai: {$user->officer_name} ({$user->role}, @{$user->username})",
            Auth::id(),
            Auth::user()?->officer_name ?? Auth::user()?->username ?? 'Administrator'
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pegawai ' . $user->officer_name . ' berhasil diperbarui.');
    }

    /**
     * Delete an employee from database.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $officerName = $user->officer_name;
        $officerRole = $user->role;
        $user->delete();

        \App\Models\AuditLog::record(
            'HAPUS_PEGAWAI',
            'Manajemen Pegawai',
            "Menghapus permanen akun pegawai: {$officerName} ({$officerRole})",
            Auth::id(),
            Auth::user()?->officer_name ?? Auth::user()?->username ?? 'Administrator'
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun pegawai ' . $officerName . ' berhasil dihapus permanen.');
    }

    /**
     * Toggle employee account status between 'Aktif' and 'Blokir'.
     */
    public function toggleStatus(string $id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat memblokir akun Anda sendiri.');
        }

        $user->status = ($user->status === 'Aktif') ? 'Blokir' : 'Aktif';
        $user->save();

        $actionText = ($user->status === 'Aktif') ? 'dibuka blokirnya' : 'diblokir';

        \App\Models\AuditLog::record(
            $user->status === 'Aktif' ? 'BUKA_BLOKIR_PEGAWAI' : 'BLOKIR_PEGAWAI',
            'Manajemen Pegawai',
            "Mengubah status akun pegawai {$user->officer_name} menjadi {$user->status}",
            Auth::id(),
            Auth::user()?->officer_name ?? Auth::user()?->username ?? 'Administrator'
        );

        return redirect()->back()
            ->with('success', 'Status akun ' . $user->officer_name . ' berhasil ' . $actionText . '.');
    }

    /**
     * Unblock employee account.
     */
    public function unblock(string $id)
    {
        $user = User::findOrFail($id);
        $user->status = 'Aktif';
        $user->save();

        \App\Models\AuditLog::record(
            'BUKA_BLOKIR_PEGAWAI',
            'Manajemen Pegawai',
            "Membuka blokir akun pegawai {$user->officer_name}",
            Auth::id(),
            Auth::user()?->officer_name ?? Auth::user()?->username ?? 'Administrator'
        );

        return redirect()->back()
            ->with('success', 'Akun pegawai ' . $user->officer_name . ' berhasil dibuka blokirnya.');
    }
}
