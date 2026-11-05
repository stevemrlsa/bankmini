<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

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
        return new Envelope(
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
}
