<?php

namespace App\Filament\Resources\MemberSurveys;

use App\Filament\Concerns\HasFrenchFilamentLabels;
use App\Filament\Resources\MemberSurveys\Pages\ListMemberSurveys;
use App\Filament\Resources\MemberSurveys\Tables\MemberSurveysTable;
use App\Models\MemberSurvey;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Réponses au sondage de satisfaction du portail fidèle.
 */
class MemberSurveyResource extends Resource
{
  use HasFrenchFilamentLabels;

  protected static ?string $model = MemberSurvey::class;

  protected static ?string $navigationLabel = 'Sondages fidèles';

  protected static ?string $modelLabel = 'sondage fidèle';

  protected static ?string $pluralModelLabel = 'Sondages fidèles';

  protected static bool $hasTitleCaseModelLabel = false;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

  protected static string|\UnitEnum|null $navigationGroup = 'Administration';

  protected static ?int $navigationSort = 20;

  protected static ?string $slug = 'sondages-fideles';

  /**
   * Lecture seule — pas de création manuelle.
   */
  public static function form(Schema $schema): Schema
  {
    return $schema->components([]);
  }

  public static function table(Table $table): Table
  {
    return MemberSurveysTable::configure($table);
  }

  public static function getPages(): array
  {
    return [
      'index' => ListMemberSurveys::route('/'),
    ];
  }

  /**
   * Uniquement les réponses complètes.
   */
  public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
  {
    return parent::getEloquentQuery()->whereNotNull('submitted_at');
  }
}
