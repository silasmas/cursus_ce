<?php

namespace App\Filament\Resources\MentoringReports;

use App\Filament\Resources\MentoringReports\Pages\CreateMentoringReport;
use App\Filament\Resources\MentoringReports\Pages\EditMentoringReport;
use App\Filament\Resources\MentoringReports\Pages\ListMentoringReports;
use App\Filament\Resources\MentoringReports\Schemas\MentoringReportForm;
use App\Filament\Resources\MentoringReports\Tables\MentoringReportsTable;
use App\Models\MentoringReport;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MentoringReportResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = MentoringReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentorat';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return MentoringReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MentoringReportsTable::configure($table);
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
            'index' => ListMentoringReports::route('/'),
            'create' => CreateMentoringReport::route('/create'),
            'edit' => EditMentoringReport::route('/{record}/edit'),
        ];
    }
}
