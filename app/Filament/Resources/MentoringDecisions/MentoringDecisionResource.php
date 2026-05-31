<?php

namespace App\Filament\Resources\MentoringDecisions;

use App\Filament\Resources\MentoringDecisions\Pages\CreateMentoringDecision;
use App\Filament\Resources\MentoringDecisions\Pages\EditMentoringDecision;
use App\Filament\Resources\MentoringDecisions\Pages\ListMentoringDecisions;
use App\Filament\Resources\MentoringDecisions\Schemas\MentoringDecisionForm;
use App\Filament\Resources\MentoringDecisions\Tables\MentoringDecisionsTable;
use App\Models\MentoringDecision;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MentoringDecisionResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = MentoringDecision::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentorat';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return MentoringDecisionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MentoringDecisionsTable::configure($table);
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
            'index' => ListMentoringDecisions::route('/'),
            'create' => CreateMentoringDecision::route('/create'),
            'edit' => EditMentoringDecision::route('/{record}/edit'),
        ];
    }
}
