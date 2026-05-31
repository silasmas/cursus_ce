<?php

namespace Database\Seeders;

use App\Models\LegalDocument;
use App\Services\Legal\LegalDocumentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Importe le règlement intérieur ECAP (PDF) pour l'acceptation à l'inscription.
 */
class LegalDocumentSeeder extends Seeder
{
  /**
   * Copie le PDF source et active la version en base.
   */
  public function run(): void
  {
    $candidates = [
      database_path('seeders/assets/ecap-reglement-interieur.pdf'),
      'C:\\Users\\ZBOOK\\Downloads\\phila\\REGLEMENT D\'ORDRE INTERIEUR ECAP UPDATE 2024-1.pdf',
    ];

    $sourcePath = null;

    foreach ($candidates as $path) {
      if (is_string($path) && File::isFile($path)) {
        $sourcePath = $path;
        break;
      }
    }

    if ($sourcePath === null) {
      $this->command?->warn('PDF du règlement ECAP introuvable — placez-le dans database/seeders/assets/ecap-reglement-interieur.pdf');

      return;
    }

    $storageRelative = 'legal-documents/ecap-reglement-interieur-2024-1.pdf';
    Storage::disk('public')->makeDirectory('legal-documents');
    Storage::disk('public')->put($storageRelative, File::get($sourcePath));

    LegalDocument::query()->updateOrCreate(
      ['slug' => LegalDocumentService::ECAP_ROI_SLUG, 'version' => '2024-1'],
      [
        'title' => 'Règlement d\'ordre intérieur ECAP',
        'summary' => 'Document obligatoire avant toute inscription sur PHILA-CE.',
        'file_path' => $storageRelative,
        'is_active' => true,
        'required_at_registration' => true,
        'published_at' => now(),
      ],
    );
  }
}
