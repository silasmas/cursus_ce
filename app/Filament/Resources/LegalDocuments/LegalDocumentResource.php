<?php

namespace App\Filament\Resources\LegalDocuments;

use App\Filament\Concerns\HasFrenchFilamentLabels;
use App\Filament\Resources\LegalDocuments\Pages\CreateLegalDocument;
use App\Filament\Resources\LegalDocuments\Pages\EditLegalDocument;
use App\Filament\Resources\LegalDocuments\Pages\ListLegalDocuments;
use App\Filament\Resources\LegalDocuments\Schemas\LegalDocumentForm;
use App\Filament\Resources\LegalDocuments\Tables\LegalDocumentsTable;
use App\Models\LegalDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Ressource Filament : documents légaux (règlement intérieur ECAP).
 */
class LegalDocumentResource extends Resource
{
  use HasFrenchFilamentLabels;

  protected static ?string $model = LegalDocument::class;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

  protected static string|\UnitEnum|null $navigationGroup = 'Plateforme';

  protected static ?string $recordTitleAttribute = 'title';

  protected static ?string $modelLabel = 'document légal';

  protected static ?string $pluralModelLabel = 'documents légaux';

  /**
   * Formulaire de création / édition.
   */
  public static function form(Schema $schema): Schema
  {
    return LegalDocumentForm::configure($schema);
  }

  /**
   * Tableau de liste.
   */
  public static function table(Table $table): Table
  {
    return LegalDocumentsTable::configure($table);
  }

  /**
   * Pages de la ressource.
   *
   * @return array<string, class-string>
   */
  public static function getPages(): array
  {
    return [
      'index' => ListLegalDocuments::route('/'),
      'create' => CreateLegalDocument::route('/create'),
      'edit' => EditLegalDocument::route('/{record}/edit'),
    ];
  }
}
