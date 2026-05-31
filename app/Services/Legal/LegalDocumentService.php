<?php

namespace App\Services\Legal;

use App\Models\LegalDocument;

/**
 * Accès aux documents légaux actifs pour inscription et affichage public.
 */
class LegalDocumentService
{
  /**
   * Slug du règlement intérieur ECAP.
   */
  public const ECAP_ROI_SLUG = 'ecap_reglement_interieur';

  /**
   * Document actif obligatoire à l'inscription, s'il existe.
   *
   * @return array<string, mixed>|null
   */
  public function registrationPayload(): ?array
  {
    $document = $this->activeRegistrationDocument();

    if ($document === null) {
      return null;
    }

    return $this->toPayload($document);
  }

  /**
   * Document actif pour l'inscription.
   */
  public function activeRegistrationDocument(): ?LegalDocument
  {
    return LegalDocument::query()
      ->where('slug', self::ECAP_ROI_SLUG)
      ->where('is_active', true)
      ->where('required_at_registration', true)
      ->orderByDesc('published_at')
      ->orderByDesc('id')
      ->first();
  }

  /**
   * Document actif par slug.
   */
  public function activeBySlug(string $slug): ?LegalDocument
  {
    return LegalDocument::query()
      ->where('slug', $slug)
      ->where('is_active', true)
      ->orderByDesc('published_at')
      ->orderByDesc('id')
      ->first();
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(LegalDocument $document): array
  {
    return [
      'id' => $document->id,
      'slug' => $document->slug,
      'title' => $document->title,
      'summary' => $document->summary,
      'version' => $document->version,
      'url' => route('legal-documents.show', $document->slug),
      'download_url' => route('legal-documents.download', $document->slug),
    ];
  }
}
