<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * O código de 6 dígitos do sign-up livre. Sem view/Blade de propósito: é uma
 * mensagem só, e `htmlString` evita registrar um namespace de views só para
 * isto — MAIL_MAILER=log no dev/demo, então o código aparece direto no log.
 */
final class VerificationCodeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Seu código de verificação — CampusOS');
    }

    public function content(): Content
    {
        return new Content(htmlString: <<<HTML
            <p>Use o código abaixo para confirmar seu e-mail no CampusOS:</p>
            <p style="font-size:28px; font-weight:bold; letter-spacing:4px;">{$this->code}</p>
            <p>Ele expira em 15 minutos. Se você não pediu este cadastro, ignore este e-mail.</p>
            HTML,
        );
    }
}
