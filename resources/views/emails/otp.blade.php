<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Reset Kata Sandi</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #334155;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f6f9; padding: 40px 15px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" width="100%" style="max-width: 560px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #0d6efd; padding: 32px 40px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.5px;">
                                Bank Mini Sekolah
                            </h1>
                            <p style="margin: 6px 0 0; color: rgba(255, 255, 255, 0.85); font-size: 13px;">
                                Sistem Layanan E-Teller &amp; Perbankan Siswa
                            </p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 40px 32px;">
                            <h2 style="margin: 0 0 16px; font-size: 18px; font-weight: 600; color: #0f172a;">
                                Halo, {{ $officerName }} 👋
                            </h2>
                            <p style="margin: 0 0 20px; font-size: 14px; line-height: 1.6; color: #475569;">
                                Kami menerima permintaan untuk {{ $purpose ?? 'mengatur ulang kata sandi akun internal' }} Anda dengan peran <strong>{{ $roleName }}</strong>. Gunakan kode verifikasi One-Time Password (OTP) berikut untuk melanjutkan:
                            </p>

                            <!-- OTP Box -->
                            <div style="margin: 28px 0; text-align: center;">
                                <div style="display: inline-block; background-color: #f8fafc; border: 2px dashed #93c5fd; border-radius: 12px; padding: 18px 32px;">
                                    <span style="font-family: 'Courier New', Courier, monospace; font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #0d6efd;">
                                        {{ $otp }}
                                    </span>
                                </div>
                                <p style="margin: 10px 0 0; font-size: 12px; color: #64748b;">
                                    Kode OTP ini berlaku selama <strong>2 menit (120 detik)</strong>.
                                </p>
                            </div>

                            <!-- Security Warning -->
                            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; border-radius: 6px; padding: 14px 16px; margin-bottom: 24px;">
                                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #991b1b;">
                                    <strong>Penting:</strong> Jangan pernah memberikan kode ini kepada siapa pun, termasuk pengelola Bank Mini. Petugas kami tidak akan pernah meminta kode OTP Anda.
                                </p>
                            </div>

                            <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #64748b;">
                                Jika Anda tidak merasa melakukan permintaan pengaturan ulang kata sandi ini, silakan abaikan email ini. Akun Anda tetap aman dan kata sandi tidak akan berubah.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 40px; text-align: center;">
                            <p style="margin: 0 0 4px; font-size: 12px; color: #94a3b8;">
                                Email ini dikirim secara otomatis oleh Sistem E-Teller Bank Mini Sekolah.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                                &copy; {{ date('Y') }} Bank Mini - SMK Negeri 1 Bantul. Seluruh hak cipta dilindungi.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

