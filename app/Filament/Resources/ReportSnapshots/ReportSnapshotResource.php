<?php

namespace App\Filament\Resources\ReportSnapshots;

use App\Filament\Resources\ReportSnapshots\Pages\CreateReportSnapshot;
use App\Filament\Resources\ReportSnapshots\Pages\EditReportSnapshot;
use App\Filament\Resources\ReportSnapshots\Pages\ListReportSnapshots;
use App\Filament\Resources\ReportSnapshots\Schemas\ReportSnapshotForm;
use App\Filament\Resources\ReportSnapshots\Tables\ReportSnapshotsTable;
use App\Models\ReportSnapshot;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReportSnapshotResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = ReportSnapshot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCamera;

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ReportSnapshotForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportSnapshotsTable::configure($table);
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
            'index' => ListReportSnapshots::route('/'),
            'create' => CreateReportSnapshot::route('/create'),
            'edit' => EditReportSnapshot::route('/{record}/edit'),
        ];
    }
}
