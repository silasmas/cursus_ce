<?php

namespace App\Filament\Resources\MentoringFeedback;

use App\Filament\Resources\MentoringFeedback\Pages\CreateMentoringFeedback;
use App\Filament\Resources\MentoringFeedback\Pages\EditMentoringFeedback;
use App\Filament\Resources\MentoringFeedback\Pages\ListMentoringFeedback;
use App\Filament\Resources\MentoringFeedback\Schemas\MentoringFeedbackForm;
use App\Filament\Resources\MentoringFeedback\Tables\MentoringFeedbackTable;
use App\Models\MentoringFeedback;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MentoringFeedbackResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = MentoringFeedback::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentorat';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return MentoringFeedbackForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MentoringFeedbackTable::configure($table);
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
            'index' => ListMentoringFeedback::route('/'),
            'create' => CreateMentoringFeedback::route('/create'),
            'edit' => EditMentoringFeedback::route('/{record}/edit'),
        ];
    }
}
