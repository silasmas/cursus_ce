<?php

namespace App\Filament\Resources\AssignmentSubmissions;

use App\Filament\Resources\AssignmentSubmissions\Pages\CreateAssignmentSubmission;
use App\Filament\Resources\AssignmentSubmissions\Pages\EditAssignmentSubmission;
use App\Filament\Resources\AssignmentSubmissions\Pages\ListAssignmentSubmissions;
use App\Filament\Resources\AssignmentSubmissions\Schemas\AssignmentSubmissionForm;
use App\Filament\Resources\AssignmentSubmissions\Tables\AssignmentSubmissionsTable;
use App\Models\AssignmentSubmission;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssignmentSubmissionResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = AssignmentSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;

    protected static string|\UnitEnum|null $navigationGroup = 'Évaluations';

    protected static ?int $navigationSort = 60;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AssignmentSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssignmentSubmissionsTable::configure($table);
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
            'index' => ListAssignmentSubmissions::route('/'),
            'create' => CreateAssignmentSubmission::route('/create'),
            'edit' => EditAssignmentSubmission::route('/{record}/edit'),
        ];
    }
}
