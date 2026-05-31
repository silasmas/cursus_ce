<?php

namespace App\Filament\Resources\CourseModules;

use App\Filament\Resources\CourseModules\Pages\CreateCourseModule;
use App\Filament\Resources\CourseModules\Pages\EditCourseModule;
use App\Filament\Resources\CourseModules\Pages\ListCourseModules;
use App\Filament\Resources\CourseModules\RelationManagers\ChaptersRelationManager;
use App\Filament\Resources\CourseModules\RelationManagers\ModuleExitQuizRelationManager;
use App\Filament\Resources\CourseModules\Schemas\CourseModuleForm;
use App\Filament\Resources\CourseModules\Tables\CourseModulesTable;
use App\Models\CourseModule;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseModuleResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = CourseModule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static string|\UnitEnum|null $navigationGroup = 'Contenu pédagogique';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CourseModuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourseModulesTable::configure($table);
    }

    /**
     * Précharge le cursus pour l'affichage des groupes.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('course');
    }

    public static function getRelations(): array
    {
        return [
            ChaptersRelationManager::class,
            ModuleExitQuizRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseModules::route('/'),
            'create' => CreateCourseModule::route('/create'),
            'edit' => EditCourseModule::route('/{record}/edit'),
        ];
    }
}
