<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $officerName;
    public string $roleName;
    public string $purpose;
    public string $subjectText;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $otp, 
        string $officerName = 'Pegawai', 
        string $roleName = 'Pegawai',
        string $purpose = 'mengatur ulang kata sandi akun',
        string $subjectText = 'Kode OTP Verifikasi Keamanan - Bank Mini Sekolah'
    ) {
        $this->otp = $otp;
        $this->officerName = $officerName;
        $this->roleName = $roleName;
        $this->purpose = $purpose;
        $this->subjectText = $subjectText;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        if (empty($fromAddress) || $fromAddress === 'hello@example.com') {
            $fromAddress = 'bankminismkn1bantul@gmail.com';
        }
        $fromName = config('mail.from.name');
        if (empty($fromName) || $fromName === 'Laravel') {
            $fromName = 'Bank Mini SMKN 1 Bantul';
        }

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: $this->subjectText,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Kirim email OTP dengan fallback otomatis (Port 587 STARTTLS -> Port 465 SSL)
     * dan memastikan konfigurasi SMTP Gmail aktif meskipun .env server belum sinkron.
     */
    public static function sendReliably(
        string $recipientEmail,
        string $otp,
        string $officerName = 'Pengguna',
        string $roleName = 'Pengguna',
        string $purpose = 'mengatur ulang kata sandi akun',
        string $subjectText = 'Kode OTP Verifikasi Keamanan - Bank Mini Sekolah'
    ): void {
        $recipientEmail = trim($recipientEmail);

        $defaultMailer = config('mail.default');
        $smtpHost = config('mail.mailers.smtp.host');
        $smtpUser = config('mail.mailers.smtp.username');

        if (
            in_array($defaultMailer, ['log', 'array', null, ''], true) ||
            empty($smtpUser) ||
            in_array($smtpHost, ['127.0.0.1', 'localhost', 'mailpit', null, ''], true)
        ) {
            config([
                'mail.default'                  => 'smtp',
                'mail.mailers.smtp.transport'   => 'smtp',
                'mail.mailers.smtp.scheme'      => 'smtp',
                'mail.mailers.smtp.host'        => 'smtp.gmail.com',
                'mail.mailers.smtp.port'        => 587,
                'mail.mailers.smtp.username'    => 'bankminismkn1bantul@gmail.com',
                'mail.mailers.smtp.password'    => 'mzucjnknxohplahj',
                'mail.mailers.smtp.timeout'     => 15,
                'mail.mailers.smtp.verify_peer' => false,
                'mail.from.address'             => 'bankminismkn1bantul@gmail.com',
                'mail.from.name'                => 'Bank Mini SMKN 1 Bantul',
            ]);
            try {
                app('mail.manager')->forgetMailers();
            } catch (\Throwable $e) {
            }
        } else {
            config(['mail.mailers.smtp.verify_peer' => false]);
        }

        $mailable = new self($otp, $officerName, $roleName, $purpose, $subjectText);

        try {
            Mail::mailer('smtp')->to($recipientEmail)->send($mailable);
        } catch (\Throwable $firstError) {
            Log::warning('SMTP primary attempt failed for ' . $recipientEmail . ': ' . $firstError->getMessage() . ' — Retrying via SMTPS port 465...');

            // Fallback ke port 465 (SMTPS / SSL) jika port 587 diblokir atau gagal handshake
            config([
                'mail.mailers.smtp.scheme'      => 'smtps',
                'mail.mailers.smtp.host'        => config('mail.mailers.smtp.host') ?: 'smtp.gmail.com',
                'mail.mailers.smtp.port'        => 465,
                'mail.mailers.smtp.username'    => config('mail.mailers.smtp.username') ?: 'bankminismkn1bantul@gmail.com',
                'mail.mailers.smtp.password'    => config('mail.mailers.smtp.password') ?: 'mzucjnknxohplahj',
                'mail.mailers.smtp.timeout'     => 15,
                'mail.mailers.smtp.verify_peer' => false,
            ]);
            try {
                app('mail.manager')->forgetMailers();
            } catch (\Throwable $e) {
            }

            Mail::mailer('smtp')->to($recipientEmail)->send(new self($otp, $officerName, $roleName, $purpose, $subjectText));
        }
    }
}
