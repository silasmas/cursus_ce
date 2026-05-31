<?php

namespace App\Filament\Resources\LearningGroupMembers;

use App\Filament\Resources\LearningGroupMembers\Pages\CreateLearningGroupMember;
use App\Filament\Resources\LearningGroupMembers\Pages\EditLearningGroupMember;
use App\Filament\Resources\LearningGroupMembers\Pages\ListLearningGroupMembers;
use App\Filament\Resources\LearningGroupMembers\Schemas\LearningGroupMemberForm;
use App\Filament\Resources\LearningGroupMembers\Tables\LearningGroupMembersTable;
use App\Models\LearningGroupMember;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LearningGroupMemberResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = LearningGroupMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Apprenants';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return LearningGroupMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LearningGroupMembersTable::configure($table);
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
            'index' => ListLearningGroupMembers::route('/'),
            'create' => CreateLearningGroupMember::route('/create'),
            'edit' => EditLearningGroupMember::route('/{record}/edit'),
        ];
    }
}
