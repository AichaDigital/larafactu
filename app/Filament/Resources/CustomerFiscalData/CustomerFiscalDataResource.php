<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerFiscalData;

use AichaDigital\Larabill\Models\CustomerFiscalData;
use App\Filament\Resources\CustomerFiscalData\Pages\ManageCustomerFiscalData;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerFiscalDataResource extends Resource
{
    protected static ?string $model = CustomerFiscalData::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Datos Fiscales Clientes';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Cliente')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('fiscal_name')
                    ->label('Nombre Fiscal')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Puede diferir del nombre del usuario')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('tax_id')
                    ->label('NIF/CIF')
                    ->maxLength(255)
                    ->placeholder('12345678A o ESB12345678'),

                Forms\Components\TextInput::make('legal_entity_type')
                    ->label('Tipo de Entidad')
                    ->maxLength(255)
                    ->placeholder('Particular, SL, Autónomo...'),

                Forms\Components\Toggle::make('is_company')
                    ->label('Es Empresa (B2B)')
                    ->helperText('Marca si es empresa en lugar de particular'),

                Forms\Components\TextInput::make('address')
                    ->label('Dirección Fiscal')
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('city')
                    ->label('Ciudad')
                    ->maxLength(255),

                Forms\Components\TextInput::make('state')
                    ->label('Provincia/Estado')
                    ->maxLength(255),

                Forms\Components\TextInput::make('zip_code')
                    ->label('Código Postal')
                    ->maxLength(255),

                Forms\Components\TextInput::make('country_code')
                    ->label('País (ISO)')
                    ->required()
                    ->maxLength(2)
                    ->default('ES')
                    ->placeholder('ES'),

                Forms\Components\Toggle::make('is_eu_vat_registered')
                    ->label('Registro IVA UE')
                    ->helperText('Cliente con VAT ID intracomunitario (reverse charge)'),

                Forms\Components\Toggle::make('is_exempt_vat')
                    ->label('Exento de IVA')
                    ->helperText('Cliente con exención fiscal'),

                Forms\Components\DatePicker::make('valid_from')
                    ->label('Vigente Desde')
                    ->required()
                    ->default(now())
                    ->helperText('Fecha de inicio de vigencia'),

                Forms\Components\DatePicker::make('valid_until')
                    ->label('Vigente Hasta')
                    ->helperText('Dejar vacío para configuración actual activa'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true),

                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('Motivo del cambio: cambio de domicilio, CIF, etc.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fiscal_name')
                    ->label('Nombre Fiscal')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tax_id')
                    ->label('NIF/CIF')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_company')
                    ->label('Empresa')
                    ->boolean(),

                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Vigente Desde')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Vigente Hasta')
                    ->date('d/m/Y')
                    ->default('Actual')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_eu_vat_registered')
                    ->label('VAT UE')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Cliente')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activa')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo históricas'),

                Tables\Filters\TernaryFilter::make('is_company')
                    ->label('Tipo')
                    ->placeholder('Todas')
                    ->trueLabel('Solo empresas')
                    ->falseLabel('Solo particulares'),

                Tables\Filters\TernaryFilter::make('is_eu_vat_registered')
                    ->label('VAT UE'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerFiscalData::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $active = CustomerFiscalData::where('is_active', true)
            ->whereNull('valid_until')
            ->count();

        return $active > 0 ? (string) $active : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
