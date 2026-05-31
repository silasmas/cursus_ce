<?php

namespace App\Filament\Resources\MediaAssets;

use App\Filament\Resources\MediaAssets\Pages\CreateMediaAsset;
use App\Filament\Resources\MediaAssets\Pages\EditMediaAsset;
use App\Filament\Resources\MediaAssets\Pages\ListMediaAssets;
use App\Filament\Resources\MediaAssets\Schemas\MediaAssetForm;
use App\Filament\Resources\MediaAssets\Tables\MediaAssetsTable;
use App\Models\MediaAsset;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MediaAssetResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = MediaAsset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Contenu pédagogique';

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'path';

    public static function form(Schema $schema): Schema
    {
        return MediaAssetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaAssetsTable::configure($table);
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
            'index' => ListMediaAssets::route('/'),
            'create' => CreateMediaAsset::route('/create'),
            'edit' => EditMediaAsset::route('/{record}/edit'),
        ];
    }
}
