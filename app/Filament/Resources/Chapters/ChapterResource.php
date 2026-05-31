<?php

namespace App\Filament\Resources\Chapters;

use App\Filament\Resources\Chapters\Pages\CreateChapter;
use App\Filament\Resources\Chapters\Pages\EditChapter;
use App\Filament\Resources\Chapters\Pages\ListChapters;
use App\Filament\Resources\Chapters\RelationManagers\ContentBlocksRelationManager;
use App\Filament\Resources\Chapters\Schemas\ChapterForm;
use App\Filament\Resources\Chapters\Tables\ChaptersTable;
use App\Models\Chapter;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChapterResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = Chapter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Contenu pédagogique';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ChapterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChaptersTable::configure($table);
    }

    /**
     * Précharge cours et module pour les en-têtes de groupe.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['course', 'courseModule']);
    }

    public static function getRelations(): array
    {
        return [
            ContentBlocksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChapters::route('/'),
            'create' => CreateChapter::route('/create'),
            'edit' => EditChapter::route('/{record}/edit'),
        ];
    }
}
