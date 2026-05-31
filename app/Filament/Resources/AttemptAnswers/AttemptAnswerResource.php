<?php

namespace App\Filament\Resources\AttemptAnswers;

use App\Filament\Resources\AttemptAnswers\Pages\CreateAttemptAnswer;
use App\Filament\Resources\AttemptAnswers\Pages\EditAttemptAnswer;
use App\Filament\Resources\AttemptAnswers\Pages\ListAttemptAnswers;
use App\Filament\Resources\AttemptAnswers\Schemas\AttemptAnswerForm;
use App\Filament\Resources\AttemptAnswers\Tables\AttemptAnswersTable;
use App\Models\AttemptAnswer;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AttemptAnswerResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = AttemptAnswer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Évaluations';

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AttemptAnswerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttemptAnswersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttemptAnswers::route('/'),
            'create' => CreateAttemptAnswer::route('/create'),
            'edit' => EditAttemptAnswer::route('/{record}/edit'),
        ];
    }
}
