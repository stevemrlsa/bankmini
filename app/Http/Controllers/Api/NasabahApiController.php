<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Saver;
use App\Models\Transaction;
use App\Http\Resources\SaverResource;
use Illuminate\Support\Facades\Hash;

class NasabahApiController extends Controller
{
    public function getProfile(Request $request)
    {
        $saver = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Profil dan saldo berhasil diambil.',
            'data' => new SaverResource($saver)
        ], 200);
    }

    public function getMutasi(Request $request)
    {
        $saver = $request->user();
        
        $transactions = Transaction::where('saver_id', $saver->id)
        ->orderBy('transaction_date', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat mutasi berhasil diambil',
            'data' => $transactions
        ], 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string',
            'security_pin' => 'required|digits:6',
        ]);

        $saver = Saver::where('account_number', $request->account_number)->first();

        if (!$saver || !Hash::check($request->security_pin, $saver->security_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor rekening atau PIN salah.',
            ], 403);
        }

        $saver->tokens()->delete();

        $token = $saver->createToken('saver-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login nasabah berhasil',
            'token' => $token,
            'data' => new SaverResource($saver)
        ], 200);
    }
}


