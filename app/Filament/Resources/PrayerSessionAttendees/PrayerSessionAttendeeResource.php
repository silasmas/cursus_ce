<?php

namespace App\Filament\Resources\PrayerSessionAttendees;

use App\Filament\Resources\PrayerSessionAttendees\Pages\CreatePrayerSessionAttendee;
use App\Filament\Resources\PrayerSessionAttendees\Pages\EditPrayerSessionAttendee;
use App\Filament\Resources\PrayerSessionAttendees\Pages\ListPrayerSessionAttendees;
use App\Filament\Resources\PrayerSessionAttendees\Schemas\PrayerSessionAttendeeForm;
use App\Filament\Resources\PrayerSessionAttendees\Tables\PrayerSessionAttendeesTable;
use App\Models\PrayerSessionAttendee;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PrayerSessionAttendeeResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = PrayerSessionAttendee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static string|\UnitEnum|null $navigationGroup = 'Prière';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PrayerSessionAttendeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrayerSessionAttendeesTable::configure($table);
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
            'index' => ListPrayerSessionAttendees::route('/'),
            'create' => CreatePrayerSessionAttendee::route('/create'),
            'edit' => EditPrayerSessionAttendee::route('/{record}/edit'),
        ];
    }
}
