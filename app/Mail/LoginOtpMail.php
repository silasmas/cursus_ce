<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * E-mail contenant le code OTP de connexion PHILA-CE.
 */
class LoginOtpMail extends Mailable
{
  use Queueable, SerializesModels;

  /**
   * @param  string  $code  Code OTP à six chiffres
   * @param  int  $expiryMinutes  Durée de validité en minutes
   */
  public function __construct(
    public string $code,
    public int $expiryMinutes,
  ) {}

  /**
   * Enveloppe de l'e-mail OTP.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      subject: 'Votre code de connexion PHILA-CE',
    );
  }

  /**
   * Contenu HTML de l'e-mail OTP.
   */
  public function content(): Content
  {
    return new Content(
      view: 'emails.login-otp',
    );
  }
}
