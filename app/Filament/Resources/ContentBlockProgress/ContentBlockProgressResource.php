<?php

namespace App\Filament\Resources\ContentBlockProgress;

use App\Filament\Resources\ContentBlockProgress\Pages\CreateContentBlockProgress;
use App\Filament\Resources\ContentBlockProgress\Pages\EditContentBlockProgress;
use App\Filament\Resources\ContentBlockProgress\Pages\ListContentBlockProgress;
use App\Filament\Resources\ContentBlockProgress\Schemas\ContentBlockProgressForm;
use App\Filament\Resources\ContentBlockProgress\Tables\ContentBlockProgressTable;
use App\Models\ContentBlockProgress;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContentBlockProgressResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = ContentBlockProgress::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static string|\UnitEnum|null $navigationGroup = 'Progression';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ContentBlockProgressForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentBlockProgressTable::configure($table);
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
            'index' => ListContentBlockProgress::route('/'),
            'create' => CreateContentBlockProgress::route('/create'),
            'edit' => EditContentBlockProgress::route('/{record}/edit'),
        ];
    }
}
