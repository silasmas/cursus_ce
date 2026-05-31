<?php

namespace App\Filament\Resources\PrayerSessions;

use App\Filament\Resources\PrayerSessions\Pages\CreatePrayerSession;
use App\Filament\Resources\PrayerSessions\Pages\EditPrayerSession;
use App\Filament\Resources\PrayerSessions\Pages\ListPrayerSessions;
use App\Filament\Resources\PrayerSessions\Schemas\PrayerSessionForm;
use App\Filament\Resources\PrayerSessions\Tables\PrayerSessionsTable;
use App\Models\PrayerSession;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PrayerSessionResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = PrayerSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|\UnitEnum|null $navigationGroup = 'Prière';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PrayerSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrayerSessionsTable::configure($table);
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
            'index' => ListPrayerSessions::route('/'),
            'create' => CreatePrayerSession::route('/create'),
            'edit' => EditPrayerSession::route('/{record}/edit'),
        ];
    }
}
