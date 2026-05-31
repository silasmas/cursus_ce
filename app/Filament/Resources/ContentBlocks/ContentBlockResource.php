<?php

namespace App\Filament\Resources\ContentBlocks;

use App\Filament\Resources\ContentBlocks\Pages\CreateContentBlock;
use App\Filament\Resources\ContentBlocks\Pages\EditContentBlock;
use App\Filament\Resources\ContentBlocks\Pages\ListContentBlocks;
use App\Filament\Resources\ContentBlocks\Schemas\ContentBlockForm;
use App\Filament\Resources\ContentBlocks\Tables\ContentBlocksTable;
use App\Models\ContentBlock;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContentBlockResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = ContentBlock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|\UnitEnum|null $navigationGroup = 'Contenu pédagogique';

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ContentBlockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentBlocksTable::configure($table);
    }

    /**
     * Précharge chapitre, module et cours pour les en-têtes de groupe.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'chapter.course',
            'chapter.courseModule',
            'mediaAsset',
        ]);
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
            'index' => ListContentBlocks::route('/'),
            'create' => CreateContentBlock::route('/create'),
            'edit' => EditContentBlock::route('/{record}/edit'),
        ];
    }
}
