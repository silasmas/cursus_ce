<?php

namespace App\Filament\Resources\VacationQuestions;

use App\Filament\Concerns\HasFrenchFilamentLabels;
use App\Filament\Resources\VacationQuestions\Pages\CreateVacationQuestion;
use App\Filament\Resources\VacationQuestions\Pages\EditVacationQuestion;
use App\Filament\Resources\VacationQuestions\Pages\ListVacationQuestions;
use App\Filament\Resources\VacationQuestions\Schemas\VacationQuestionForm;
use App\Filament\Resources\VacationQuestions\Tables\VacationQuestionsTable;
use App\Models\VacationQuestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Questions & réponses des acteurs de vacation ECAP (M6).
 */
class VacationQuestionResource extends Resource
{
  use HasFrenchFilamentLabels;

  protected static ?string $model = VacationQuestion::class;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

  protected static string|\UnitEnum|null $navigationGroup = 'ECAP';

  protected static ?int $navigationSort = 3;

  protected static ?string $slug = 'questions-vacation-ecap';

  /**
   * @return array<string, \Filament\Resources\Pages\PageRegistration>
   */
  public static function getPages(): array
  {
    return [
      'index' => ListVacationQuestions::route('/'),
      'create' => CreateVacationQuestion::route('/create'),
      'edit' => EditVacationQuestion::route('/{record}/edit'),
    ];
  }

  public static function form(Schema $schema): Schema
  {
    return VacationQuestionForm::configure($schema);
  }

  public static function table(Table $table): Table
  {
    return VacationQuestionsTable::configure($table);
  }
}
