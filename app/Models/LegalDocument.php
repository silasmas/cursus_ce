<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Document légal versionné (règlement intérieur ECAP, etc.).
 */
class LegalDocument extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'slug',
    'title',
    'summary',
    'file_path',
    'version',
    'is_active',
    'required_at_registration',
    'published_at',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'is_active' => 'boolean',
      'required_at_registration' => 'boolean',
      'published_at' => 'datetime',
    ];
  }

  /**
   * Profils ayant accepté cette version.
   */
  public function profiles(): HasMany
  {
    return $this->hasMany(Profile::class, 'accepted_legal_document_id');
  }

  /**
   * URL publique du fichier PDF.
   */
  public function publicUrl(): string
  {
    return asset('storage/'.$this->file_path);
  }

  /**
   * Vérifie que le fichier existe sur le disque public.
   */
  public function fileExists(): bool
  {
    return Storage::disk('public')->exists($this->file_path);
  }
}
