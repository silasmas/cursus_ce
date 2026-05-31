<?php

namespace App\Filament\Resources\Programs;

use App\Filament\Resources\Programs\Pages\CreateProgram;
use App\Filament\Resources\Programs\Pages\EditProgram;
use App\Filament\Resources\Programs\Pages\ListPrograms;
use App\Filament\Resources\Programs\RelationManagers\CoursesRelationManager;
use App\Filament\Resources\Programs\Schemas\ProgramForm;
use App\Filament\Resources\Programs\Tables\ProgramsTable;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use App\Models\Program;
use App\Services\Program\MergeApollosCeProgramService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Administration des cursus PHILA-CE (5 parcours + paramètres).
 */
class ProgramResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = Program::class;

    protected static ?string $navigationLabel = 'Gestion des cursus';

    protected static ?string $modelLabel = 'cursus';

    protected static ?string $pluralModelLabel = 'Gestion des cursus';

    protected static ?string $slug = 'gestion-cursus';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion des cursus';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Masque l'ancien doublon « apollos-ce » (fusionné dans ecap).
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('slug', '!=', MergeApollosCeProgramService::LEGACY_APOLLOS_SLUG);
    }

    public static function form(Schema $schema): Schema
    {
        return ProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CoursesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrograms::route('/'),
            'create' => CreateProgram::route('/create'),
            'edit' => EditProgram::route('/{record}/edit'),
        ];
    }
}
