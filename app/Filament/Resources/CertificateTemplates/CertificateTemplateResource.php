<?php

namespace App\Filament\Resources\CertificateTemplates;

use App\Filament\Resources\CertificateTemplates\Pages\CreateCertificateTemplate;
use App\Filament\Resources\CertificateTemplates\Pages\EditCertificateTemplate;
use App\Filament\Resources\CertificateTemplates\Pages\ListCertificateTemplates;
use App\Filament\Resources\CertificateTemplates\Schemas\CertificateTemplateForm;
use App\Filament\Resources\CertificateTemplates\Tables\CertificateTemplatesTable;
use App\Models\CertificateTemplate;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CertificateTemplateResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = CertificateTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static string|\UnitEnum|null $navigationGroup = 'Certifications';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CertificateTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CertificateTemplatesTable::configure($table);
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
            'index' => ListCertificateTemplates::route('/'),
            'create' => CreateCertificateTemplate::route('/create'),
            'edit' => EditCertificateTemplate::route('/{record}/edit'),
        ];
    }
}
