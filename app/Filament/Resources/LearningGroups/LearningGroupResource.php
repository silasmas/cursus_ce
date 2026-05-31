<?php

namespace App\Filament\Resources\LearningGroups;

use App\Filament\Resources\LearningGroups\Pages\CreateLearningGroup;
use App\Filament\Resources\LearningGroups\Pages\EditLearningGroup;
use App\Filament\Resources\LearningGroups\Pages\ListLearningGroups;
use App\Filament\Resources\LearningGroups\RelationManagers\MembersRelationManager;
use App\Filament\Resources\LearningGroups\Schemas\LearningGroupForm;
use App\Filament\Resources\LearningGroups\Tables\LearningGroupsTable;
use App\Models\LearningGroup;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LearningGroupResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = LearningGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Apprenants';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LearningGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LearningGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLearningGroups::route('/'),
            'create' => CreateLearningGroup::route('/create'),
            'edit' => EditLearningGroup::route('/{record}/edit'),
        ];
    }
}
