<?php

namespace App\Filament\Resources\ChapterProgress;

use App\Filament\Resources\ChapterProgress\Pages\CreateChapterProgress;
use App\Filament\Resources\ChapterProgress\Pages\EditChapterProgress;
use App\Filament\Resources\ChapterProgress\Pages\ListChapterProgress;
use App\Filament\Resources\ChapterProgress\Schemas\ChapterProgressForm;
use App\Filament\Resources\ChapterProgress\Tables\ChapterProgressTable;
use App\Models\ChapterProgress;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChapterProgressResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = ChapterProgress::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Progression';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ChapterProgressForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChapterProgressTable::configure($table);
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
            'index' => ListChapterProgress::route('/'),
            'create' => CreateChapterProgress::route('/create'),
            'edit' => EditChapterProgress::route('/{record}/edit'),
        ];
    }
}
