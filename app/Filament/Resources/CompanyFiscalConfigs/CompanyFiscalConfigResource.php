<?php

declare(strict_types=1);

namespace App\Filament\Resources\CompanyFiscalConfigs;

use AichaDigital\Larabill\Models\CompanyFiscalConfig;
use App\Filament\Resources\CompanyFiscalConfigs\Pages\ManageCompanyFiscalConfigs;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyFiscalConfigResource extends Resource
{
    protected static ?string $model = CompanyFiscalConfig::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Configuración Empresa';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('business_name')
                    ->label('Razón Social')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('tax_id')
                    ->label('CIF/NIF')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('ESB12345678'),

                Forms\Components\TextInput::make('legal_entity_type')
                    ->label('Tipo de Entidad')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('SL, SA, Autónomo...'),

                Forms\Components\TextInput::make('address')
                    ->label('Dirección Fiscal')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('city')
                    ->label('Ciudad')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('state')
                    ->label('Provincia/Estado')
                    ->maxLength(255),

                Forms\Components\TextInput::make('zip_code')
                    ->label('Código Postal')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('country_code')
                    ->label('País (ISO)')
                    ->required()
                    ->maxLength(2)
                    ->default('ES')
                    ->placeholder('ES'),

                Forms\Components\Toggle::make('is_oss')
                    ->label('Operador OSS')
                    ->helperText('One Stop Shop (IVA intracomunitario)'),

                Forms\Components\Toggle::make('is_roi')
                    ->label('Operador ROI')
                    ->helperText('Registro de Operadores Intracomunitarios'),

                Forms\Components\TextInput::make('currency')
                    ->label('Moneda')
                    ->required()
                    ->maxLength(3)
                    ->default('EUR'),

                Forms\Components\TextInput::make('fiscal_year_start')
                    ->label('Inicio Año Fiscal (MM-DD)')
                    ->required()
                    ->maxLength(5)
                    ->default('01-01')
                    ->placeholder('01-01'),

                Forms\Components\DatePicker::make('valid_from')
                    ->label('Vigente Desde')
                    ->required()
                    ->default(now())
                    ->helperText('Fecha de inicio de vigencia de esta configuración'),

                Forms\Components\DatePicker::make('valid_until')
                    ->label('Vigente Hasta')
                    ->helperText('Dejar vacío para configuración actual activa'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true)
                    ->helperText('Solo una configuración puede estar activa sin fecha fin'),

                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('Motivo del cambio: fusión, cambio de CIF, etc.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('business_name')
                    ->label('Razón Social')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tax_id')
                    ->label('CIF/NIF')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Vigente Desde')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Vigente Hasta')
                    ->date('d/m/Y')
                    ->placeholder('Actual')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_oss')
                    ->label('OSS')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_roi')
                    ->label('ROI')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activa')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo históricas'),

                Tables\Filters\TernaryFilter::make('is_oss')
                    ->label('Operador OSS'),

                Tables\Filters\TernaryFilter::make('is_roi')
                    ->label('Operador ROI'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('valid_from', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCompanyFiscalConfigs::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $active = CompanyFiscalConfig::where('is_active', true)
            ->whereNull('valid_until')
            ->count();

        return $active > 0 ? (string) $active : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
