<?php

namespace App\Filament\Resources\StudentAcademicRecords;

use App\Filament\Resources\StudentAcademicRecords\Pages\CreateStudentAcademicRecord;
use App\Filament\Resources\StudentAcademicRecords\Pages\EditStudentAcademicRecord;
use App\Filament\Resources\StudentAcademicRecords\Pages\ListStudentAcademicRecords;
use App\Filament\Resources\StudentAcademicRecords\Schemas\StudentAcademicRecordForm;
use App\Filament\Resources\StudentAcademicRecords\Tables\StudentAcademicRecordsTable;
use App\Models\StudentAcademicRecord;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentAcademicRecordResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = StudentAcademicRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmark;

    protected static string|\UnitEnum|null $navigationGroup = 'Apprenants';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return StudentAcademicRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentAcademicRecordsTable::configure($table);
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
            'index' => ListStudentAcademicRecords::route('/'),
            'create' => CreateStudentAcademicRecord::route('/create'),
            'edit' => EditStudentAcademicRecord::route('/{record}/edit'),
        ];
    }
}
