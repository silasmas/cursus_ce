<?php

namespace App\Filament\Resources\MentorProfiles;

use App\Filament\Resources\MentorProfiles\Pages\CreateMentorProfile;
use App\Filament\Resources\MentorProfiles\Pages\EditMentorProfile;
use App\Filament\Resources\MentorProfiles\Pages\ListMentorProfiles;
use App\Filament\Resources\MentorProfiles\Schemas\MentorProfileForm;
use App\Filament\Resources\MentorProfiles\Tables\MentorProfilesTable;
use App\Models\MentorProfile;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MentorProfileResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = MentorProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentorat';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return MentorProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MentorProfilesTable::configure($table);
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
            'index' => ListMentorProfiles::route('/'),
            'create' => CreateMentorProfile::route('/create'),
            'edit' => EditMentorProfile::route('/{record}/edit'),
        ];
    }
}
