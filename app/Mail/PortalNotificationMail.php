<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * E-mail générique de notification portail PHILA-CE.
 */
class PortalNotificationMail extends Mailable
{
  use Queueable, SerializesModels;

  /**
   * @param  string  $mailSubject  Objet de l'e-mail
   * @param  string  $title  Titre affiché dans le corps
   * @param  string  $body  Message principal
   * @param  string|null  $actionUrl  Lien d'action optionnel
   * @param  string|null  $actionLabel  Libellé du bouton
   */
  public function __construct(
    public string $mailSubject,
    public string $title,
    public string $body,
    public ?string $actionUrl = null,
    public ?string $actionLabel = null,
  ) {}

  /**
   * Enveloppe de l'e-mail.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      subject: $this->mailSubject,
    );
  }

  /**
   * Contenu HTML de l'e-mail.
   */
  public function content(): Content
  {
    return new Content(
      view: 'emails.portal-notification',
    );
  }
}
