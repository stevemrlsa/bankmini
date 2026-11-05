<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Saver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\SaverResource;

class SaverController extends Controller
{
    public function index()
    {
        $savers = Saver::all();
        return response()->json([
            'success' => true,
            'message' => 'Daftar nasabah berhasil diambil',
            'data'    => SaverResource::collection($savers)
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_number' => 'required|unique:savers,account_number',
            'student_id'     => 'required|string',
            'student_name'   => 'required|string',
            'grade_class'    => 'required|string',
            'security_pin'   => 'required|digits:6',
            'saldo'          => 'required|numeric|min:0',
        ]);

        // Enkripsi PIN sebelum save
        $validated['security_pin'] = Hash::make($validated['security_pin']);

        $saver = Saver::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Nasabah berhasil didaftarkan',
            'data'    => new SaverResource($saver)
        ], 201);
    }

    public function show($id)
    {
        $saver = Saver::find($id);

        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Nasabah tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail nasabah berhasil diambil',
            'data'    => new SaverResource($saver)
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $saver = Saver::find($id);

        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Nasabah tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'account_number' => 'sometimes|required|unique:savers,account_number,' . $id,
            'student_id'     => 'sometimes|required|string',
            'student_name'   => 'sometimes|required|string',
            'grade_class'    => 'sometimes|required|string',
            'security_pin'   => 'nullable|digits:6',
            'saldo'          => 'sometimes|required|numeric|min:0',
        ]);

        // PERBAIKAN: Dead code dihapus. Eksekusi ini sebelum return.
        if (!empty($validated['security_pin'])) {
            $validated['security_pin'] = Hash::make($validated['security_pin']);
        } else {
            unset($validated['security_pin']);
        }

        $saver->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data nasabah berhasil diperbarui',
            'data'    => new SaverResource($saver)
        ], 200);
    }

    public function destroy($id)
    {
        $saver = Saver::find($id);

        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Nasabah tidak ditemukan'
            ], 404);
        }

        $saver->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nasabah berhasil dihapus'
        ], 200);
    }
}