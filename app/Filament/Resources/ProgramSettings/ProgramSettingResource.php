<?php

namespace App\Filament\Resources\ProgramSettings;

use App\Filament\Resources\ProgramSettings\Pages\CreateProgramSetting;
use App\Filament\Resources\ProgramSettings\Pages\EditProgramSetting;
use App\Filament\Resources\ProgramSettings\Pages\ListProgramSettings;
use App\Filament\Resources\ProgramSettings\Schemas\ProgramSettingForm;
use App\Filament\Resources\ProgramSettings\Tables\ProgramSettingsTable;
use App\Models\ProgramSetting;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProgramSettingResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = ProgramSetting::class;

    protected static ?string $navigationLabel = 'Configuration des cursus';

    protected static ?string $modelLabel = 'configuration de cursus';

    protected static ?string $pluralModelLabel = 'Configuration des cursus';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion des cursus';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'configuration-cursus';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ProgramSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramSettingsTable::configure($table);
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
            'index' => ListProgramSettings::route('/'),
            'create' => CreateProgramSetting::route('/create'),
            'edit' => EditProgramSetting::route('/{record}/edit'),
        ];
    }
}
