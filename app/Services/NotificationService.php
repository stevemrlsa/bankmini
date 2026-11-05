<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\TellerShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Mengambil seluruh notifikasi relevan berdasarkan peran pengguna yang sedang login.
     *
     * @param User|null $user
     * @return array{unread_count: int, notifications: Collection}
     */
    public function getNotificationsForUser(?User $user = null): array
    {
        if (!$user) {
            return [
                'unread_count'  => 0,
                'notifications' => collect(),
            ];
        }

        $role = $user->role ?? '';
        $isAdmin = in_array($role, ['Administrator', 'Supervisor', 'Admin', 'Admin Utama', 'admin_utama']);
        $isTeller = ($role === 'Teller');

        if ($isAdmin) {
            return $this->getAdminNotifications($user);
        } elseif ($isTeller) {
            return $this->getTellerNotifications($user);
        }

        return [
            'unread_count'  => 0,
            'notifications' => collect(),
        ];
    }

    /**
     * Notifikasi khusus Administrator:
     * 1. Laporan penutupan kas masuk dari teller yang berstatus 'pending'.
     * 2. Notifikasi tetap muncul selama approval kas belum disetujui atau ditolak.
     * 3. Peringatan jika terdapat selisih kas fisik vs sistem.
     * 4. Riwayat approval yang baru saja diproses (terakhir 3 hari).
     */
    protected function getAdminNotifications(User $user): array
    {
        $notifications = collect();
        $readIds = session('read_notification_ids', []);
        $lastReadTime = session('admin_notifs_read_at');

        // 1. Laporan Kas Pending (Memerlukan Tindakan / Approval)
        $pendingReports = DailyReport::with('teller')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($pendingReports as $report) {
            $tellerName = $report->teller->officer_name ?? $report->teller->username ?? 'Teller';
            $diff = $report->closing_balance_physical - $report->closing_balance_system;
            $hasDiff = ($diff !== 0);

            $diffText = '';
            if ($hasDiff) {
                $sign = $diff > 0 ? '+' : '-';
                $diffText = ' • Selisih: ' . $sign . 'Rp ' . number_format(abs($diff), 0, ',', '.');
            }

            $notifId = 'pending_report_' . $report->id;
            $isUnread = !in_array($notifId, $readIds);

            $notifications->push([
                'id'          => $notifId,
                'type'        => 'pending_approval',
                'title'       => 'Laporan Kas Masuk: ' . $tellerName,
                'message'     => 'Menunggu verifikasi penutupan kas Rp ' . number_format($report->closing_balance_physical, 0, ',', '.') . $diffText . ' (' . Carbon::parse($report->report_date)->translatedFormat('d M Y') . ').',
                'time'        => $report->created_at ? $report->created_at->diffForHumans() : 'Hari ini',
                'raw_time'    => $report->created_at ?? now(),
                'status'      => 'pending',
                'badge'       => 'Menunggu Approval',
                'badge_class' => 'bg-warning text-dark',
                'icon'        => 'bi bi-hourglass-split',
                'icon_bg'     => 'bg-warning-subtle text-warning-emphasis',
                'url'         => route('admin.approval.index'),
                'is_unread'   => $isUnread,
                'has_action'  => true,
            ]);
        }

        // 2. Riwayat Laporan yang Baru Saja Disetujui / Ditolak (Maksimal 5 terakhir dalam 3 hari)
        $recentResolved = DailyReport::with(['teller', 'admin'])
            ->whereIn('status', ['approved', 'rejected'])
            ->where('approval_at', '>=', now()->subDays(3))
            ->orderBy('approval_at', 'desc')
            ->take(5)
            ->get();

        foreach ($recentResolved as $report) {
            $tellerName = $report->teller->officer_name ?? $report->teller->username ?? 'Teller';
            $isApproved = ($report->status === 'approved');

            $notifId = 'resolved_report_' . $report->id;

            $notifications->push([
                'id'          => $notifId,
                'type'        => $isApproved ? 'report_approved' : 'report_rejected',
                'title'       => $isApproved ? 'Kas Disetujui (' . $tellerName . ')' : 'Kas Ditolak (' . $tellerName . ')',
                'message'     => $isApproved
                    ? 'Penutupan kas Rp ' . number_format($report->closing_balance_physical, 0, ',', '.') . ' telah disetujui & dipindahkan ke Brankas.'
                    : 'Penutupan kas ditolak. Catatan: ' . ($report->notes ? \Illuminate\Support\Str::limit($report->notes, 40) : '-'),
                'time'        => $report->approval_at ? Carbon::parse($report->approval_at)->diffForHumans() : 'Hari ini',
                'raw_time'    => $report->approval_at ? Carbon::parse($report->approval_at) : $report->updated_at,
                'status'      => $report->status,
                'badge'       => $isApproved ? 'Disetujui' : 'Ditolak',
                'badge_class' => $isApproved ? 'bg-success text-white' : 'bg-danger text-white',
                'icon'        => $isApproved ? 'bi bi-check2-circle' : 'bi bi-x-circle',
                'icon_bg'     => $isApproved ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger',
                'url'         => route('admin.approval.index'),
                'is_unread'   => false,
                'has_action'  => false,
            ]);
        }

        // Urutkan berdasarkan waktu terbaru
        $sorted = $notifications->sortByDesc('raw_time')->values();

        // Hitung unread count: laporan pending yang belum dibaca
        $unreadCount = $pendingReports->filter(function ($r) use ($readIds) {
            return !in_array('pending_report_' . $r->id, $readIds);
        })->count();

        return [
            'unread_count'  => $unreadCount,
            'notifications' => $sorted,
        ];
    }

    /**
     * Notifikasi khusus Teller:
     * 1. Notifikasi dari Admin ketika laporan kas disetujui (Approved).
     * 2. Notifikasi dari Admin ketika laporan kas ditolak (Rejected) beserta catatannya.
     * 3. Status laporan yang masih 'pending' menunggu respon Admin.
     */
    protected function getTellerNotifications(User $user): array
    {
        $notifications = collect();
        $readIds = session('read_notification_ids', []);
        $lastReadTime = session('teller_notifs_read_at');

        // Ambil riwayat laporan kas milik teller ini (10 laporan terakhir)
        $reports = DailyReport::with('admin')
            ->where('teller_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $unreadCount = 0;

        foreach ($reports as $report) {
            $adminName = $report->admin->officer_name ?? $report->admin->username ?? 'Administrator';
            $notifId = 'teller_report_' . $report->id . '_' . $report->status;

            // Menentukan status unread:
            // Jika statusnya approved atau rejected, dan belum ditandai baca
            $isUnread = false;
            if (in_array($report->status, ['approved', 'rejected'])) {
                $isReadBySession = in_array($notifId, $readIds);
                $isReadByTime = $lastReadTime && Carbon::parse($report->approval_at ?? $report->updated_at)->lte(Carbon::parse($lastReadTime));
                $isUnread = !$isReadBySession && !$isReadByTime;

                if ($isUnread) {
                    $unreadCount++;
                }
            }

            if ($report->status === 'approved') {
                $notifications->push([
                    'id'          => $notifId,
                    'type'        => 'report_approved',
                    'title'       => 'Laporan Kas Disetujui',
                    'message'     => 'Laporan kas fisik Rp ' . number_format($report->closing_balance_physical, 0, ',', '.') . ' tanggal ' . Carbon::parse($report->report_date)->translatedFormat('d M Y') . ' telah disetujui oleh ' . $adminName . '. Dana telah masuk ke Brankas Utama.',
                    'time'        => $report->approval_at ? Carbon::parse($report->approval_at)->diffForHumans() : $report->updated_at->diffForHumans(),
                    'raw_time'    => $report->approval_at ? Carbon::parse($report->approval_at) : $report->updated_at,
                    'status'      => 'approved',
                    'badge'       => 'Disetujui',
                    'badge_class' => 'bg-success text-white',
                    'icon'        => 'bi bi-check-circle-fill',
                    'icon_bg'     => 'bg-success-subtle text-success',
                    'url'         => route('teller.dashboard'),
                    'is_unread'   => $isUnread,
                    'has_action'  => false,
                ]);
            } elseif ($report->status === 'rejected') {
                $notifications->push([
                    'id'          => $notifId,
                    'type'        => 'report_rejected',
                    'title'       => 'Laporan Kas Ditolak',
                    'message'     => 'Laporan kas tanggal ' . Carbon::parse($report->report_date)->translatedFormat('d M Y') . ' ditolak oleh ' . $adminName . '. Catatan: "' . ($report->notes ?: 'Periksa kembali perhitungan fisik kas laci.') . '"',
                    'time'        => $report->approval_at ? Carbon::parse($report->approval_at)->diffForHumans() : $report->updated_at->diffForHumans(),
                    'raw_time'    => $report->approval_at ? Carbon::parse($report->approval_at) : $report->updated_at,
                    'status'      => 'rejected',
                    'badge'       => 'Perlu Revisi',
                    'badge_class' => 'bg-danger text-white',
                    'icon'        => 'bi bi-exclamation-octagon-fill',
                    'icon_bg'     => 'bg-danger-subtle text-danger',
                    'url'         => route('teller.report.closing'),
                    'is_unread'   => $isUnread,
                    'has_action'  => true,
                ]);
            } else {
                // Status pending: Menunggu approval dari Admin
                $notifications->push([
                    'id'          => $notifId,
                    'type'        => 'report_pending',
                    'title'       => 'Laporan Kas Terkirim',
                    'message'     => 'Laporan penutupan kas Rp ' . number_format($report->closing_balance_physical, 0, ',', '.') . ' sedang menunggu verifikasi & approval dari Administrator.',
                    'time'        => $report->created_at ? $report->created_at->diffForHumans() : 'Hari ini',
                    'raw_time'    => $report->created_at ?? now(),
                    'status'      => 'pending',
                    'badge'       => 'Menunggu Review',
                    'badge_class' => 'bg-warning text-dark',
                    'icon'        => 'bi bi-clock-history',
                    'icon_bg'     => 'bg-warning-subtle text-warning-emphasis',
                    'url'         => route('teller.dashboard'),
                    'is_unread'   => false,
                    'has_action'  => false,
                ]);
            }
        }

        // Tambahan Cerdas: Jika Teller memiliki Shift yang sedang Aktif (Open), beri status pengingat ramah
        $activeShift = TellerShift::where('user_id', $user->id)
            ->where('status', 'Open')
            ->first();

        if ($activeShift) {
            $notifications->push([
                'id'          => 'shift_open_' . $activeShift->id,
                'type'        => 'shift_open',
                'title'       => 'Laci Kasir Sedang Aktif',
                'message'     => 'Laci kasir loket sedang terbuka dengan modal awal Rp ' . number_format($activeShift->opening_balance, 0, ',', '.') . '. Ingat untuk lakukan penutupan kas di akhir jam operasional.',
                'time'        => $activeShift->opened_at ? $activeShift->opened_at->diffForHumans() : 'Hari ini',
                'raw_time'    => $activeShift->opened_at ?? now(),
                'status'      => 'info',
                'badge'       => 'Laci Terbuka',
                'badge_class' => 'bg-primary text-white',
                'icon'        => 'bi bi-shop',
                'icon_bg'     => 'bg-primary-subtle text-primary',
                'url'         => route('teller.report.closing'),
                'is_unread'   => false,
                'has_action'  => false,
            ]);
        }

        $sorted = $notifications->sortByDesc('raw_time')->values();

        return [
            'unread_count'  => $unreadCount,
            'notifications' => $sorted,
        ];
    }

    /**
     * Menandai semua notifikasi sebagai telah dibaca untuk sesi saat ini.
     */
    public function markAllAsRead(User $user): void
    {
        $role = $user->role ?? '';
        $isAdmin = in_array($role, ['Administrator', 'Supervisor', 'Admin', 'Admin Utama', 'admin_utama']);

        $nowStr = now()->toDateTimeString();

        if ($isAdmin) {
            // Catat ID laporan pending saat ini ke session read_ids
            $pendingIds = DailyReport::where('status', 'pending')->pluck('id')->map(function($id) {
                return 'pending_report_' . $id;
            })->toArray();

            $existing = session('read_notification_ids', []);
            session(['read_notification_ids' => array_unique(array_merge($existing, $pendingIds))]);
            session(['admin_notifs_read_at' => $nowStr]);
        } else {
            // Untuk teller
            $resolvedIds = DailyReport::where('teller_id', $user->id)
                ->whereIn('status', ['approved', 'rejected'])
                ->get()
                ->map(function($r) {
                    return 'teller_report_' . $r->id . '_' . $r->status;
                })->toArray();

            $existing = session('read_notification_ids', []);
            session(['read_notification_ids' => array_unique(array_merge($existing, $resolvedIds))]);
            session(['teller_notifs_read_at' => $nowStr]);
        }
    }
}

