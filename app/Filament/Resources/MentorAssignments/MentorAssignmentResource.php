<?php

namespace App\Filament\Resources\MentorAssignments;

use App\Filament\Resources\MentorAssignments\Pages\CreateMentorAssignment;
use App\Filament\Resources\MentorAssignments\Pages\EditMentorAssignment;
use App\Filament\Resources\MentorAssignments\Pages\ListMentorAssignments;
use App\Filament\Resources\MentorAssignments\RelationManagers\DecisionsRelationManager;
use App\Filament\Resources\MentorAssignments\RelationManagers\FeedbacksRelationManager;
use App\Filament\Resources\MentorAssignments\RelationManagers\ReportsRelationManager;
use App\Filament\Resources\MentorAssignments\Schemas\MentorAssignmentForm;
use App\Filament\Resources\MentorAssignments\Tables\MentorAssignmentsTable;
use App\Models\MentorAssignment;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MentorAssignmentResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = MentorAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentorat';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return MentorAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MentorAssignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ReportsRelationManager::class,
            FeedbacksRelationManager::class,
            DecisionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMentorAssignments::route('/'),
            'create' => CreateMentorAssignment::route('/create'),
            'edit' => EditMentorAssignment::route('/{record}/edit'),
        ];
    }
}
