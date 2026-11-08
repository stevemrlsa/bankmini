<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'actor_name',
        'action',
        'module',
        'detail',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Catat log aktivitas sistem secara otomatis dan aman (fail-safe).
     */
    public static function record(string $action, string $module, string $detail, ?int $userId = null, ?string $actorName = null): ?self
    {
        try {
            $user = Auth::user();
            $resolvedUserId = $userId ?? ($user ? $user->id : null);
            $resolvedActorName = $actorName ?? ($user ? ($user->officer_name ?: $user->username) : 'Sistem Otomatis');

            $ip = null;
            $ua = null;
            try {
                $ip = Request::ip();
                $ua = substr((string) Request::userAgent(), 0, 500);
            } catch (\Throwable $e) {}

            return self::create([
                'user_id'    => $resolvedUserId,
                'actor_name' => $resolvedActorName,
                'action'     => strtolower($action),
                'module'     => $module,
                'detail'     => $detail,
                'ip_address' => $ip,
                'user_agent' => $ua,
            ]);
        } catch (\Throwable $e) {
            // Pencatatan audit log tidak boleh menggagalkan transaksi utama
            \Illuminate\Support\Facades\Log::error('AuditLog record failed: ' . $e->getMessage());
            return null;
        }
    }
}

