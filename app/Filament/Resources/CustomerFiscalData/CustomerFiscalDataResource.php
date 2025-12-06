<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerFiscalData;

use AichaDigital\Larabill\Models\CustomerFiscalData;
use App\Filament\Resources\CustomerFiscalData\Pages\ManageCustomerFiscalData;
use App\Models\User;
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

class CustomerFiscalDataResource extends Resource
{
    protected static ?string $model = CustomerFiscalData::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('filament.customer_fiscal_data.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.customer_fiscal_data.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.customer_fiscal_data.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('filament.customer_fiscal_data.fields.user_id'))
                    ->options(fn () => User::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('fiscal_name')
                    ->label(__('filament.customer_fiscal_data.fields.fiscal_name'))
                    ->required()
                    ->maxLength(255)
                    ->helperText(__('filament.customer_fiscal_data.fields.fiscal_name_helper'))
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('tax_id')
                    ->label(__('filament.customer_fiscal_data.fields.tax_id'))
                    ->maxLength(255)
                    ->placeholder(__('filament.customer_fiscal_data.fields.tax_id_placeholder')),

                Forms\Components\TextInput::make('legal_entity_type')
                    ->label(__('filament.customer_fiscal_data.fields.legal_entity_type'))
                    ->maxLength(255)
                    ->placeholder(__('filament.customer_fiscal_data.fields.legal_entity_placeholder')),

                Forms\Components\Toggle::make('is_company')
                    ->label(__('filament.customer_fiscal_data.fields.is_company'))
                    ->helperText(__('filament.customer_fiscal_data.fields.is_company_helper')),

                Forms\Components\TextInput::make('address')
                    ->label(__('filament.customer_fiscal_data.fields.address'))
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('city')
                    ->label(__('filament.customer_fiscal_data.fields.city'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('state')
                    ->label(__('filament.customer_fiscal_data.fields.state'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('zip_code')
                    ->label(__('filament.customer_fiscal_data.fields.zip_code'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('country_code')
                    ->label(__('filament.customer_fiscal_data.fields.country_code'))
                    ->required()
                    ->maxLength(2)
                    ->default('ES')
                    ->placeholder(__('filament.customer_fiscal_data.fields.country_placeholder')),

                Forms\Components\Toggle::make('is_eu_vat_registered')
                    ->label(__('filament.customer_fiscal_data.fields.is_eu_vat_registered'))
                    ->helperText(__('filament.customer_fiscal_data.fields.is_eu_vat_helper')),

                Forms\Components\Toggle::make('is_exempt_vat')
                    ->label(__('filament.customer_fiscal_data.fields.is_exempt_vat'))
                    ->helperText(__('filament.customer_fiscal_data.fields.is_exempt_helper')),

                Forms\Components\DatePicker::make('valid_from')
                    ->label(__('filament.customer_fiscal_data.fields.valid_from'))
                    ->required()
                    ->default(now())
                    ->helperText(__('filament.customer_fiscal_data.fields.valid_from_helper')),

                Forms\Components\DatePicker::make('valid_until')
                    ->label(__('filament.customer_fiscal_data.fields.valid_until'))
                    ->helperText(__('filament.customer_fiscal_data.fields.valid_until_helper')),

                Forms\Components\Toggle::make('is_active')
                    ->label(__('filament.customer_fiscal_data.fields.is_active'))
                    ->default(true),

                Forms\Components\Textarea::make('notes')
                    ->label(__('filament.customer_fiscal_data.fields.notes'))
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder(__('filament.customer_fiscal_data.fields.notes_placeholder')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.customer_fiscal_data.table.customer'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fiscal_name')
                    ->label(__('filament.customer_fiscal_data.fields.fiscal_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tax_id')
                    ->label(__('filament.customer_fiscal_data.fields.tax_id'))
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_company')
                    ->label(__('filament.customer_fiscal_data.table.company'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('valid_from')
                    ->label(__('filament.customer_fiscal_data.fields.valid_from'))
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label(__('filament.customer_fiscal_data.fields.valid_until'))
                    ->date('d/m/Y')
                    ->placeholder(__('filament.customer_fiscal_data.table.valid_until_placeholder'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filament.customer_fiscal_data.fields.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_eu_vat_registered')
                    ->label(__('filament.customer_fiscal_data.table.vat_eu'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.customer_fiscal_data.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('filament.customer_fiscal_data.filters.user_id'))
                    ->options(fn () => User::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('filament.customer_fiscal_data.filters.is_active'))
                    ->placeholder(__('filament.customer_fiscal_data.filters.is_active_all'))
                    ->trueLabel(__('filament.customer_fiscal_data.filters.is_active_true'))
                    ->falseLabel(__('filament.customer_fiscal_data.filters.is_active_false')),

                Tables\Filters\TernaryFilter::make('is_company')
                    ->label(__('filament.customer_fiscal_data.filters.is_company'))
                    ->placeholder(__('filament.customer_fiscal_data.filters.is_company_all'))
                    ->trueLabel(__('filament.customer_fiscal_data.filters.is_company_true'))
                    ->falseLabel(__('filament.customer_fiscal_data.filters.is_company_false')),

                Tables\Filters\TernaryFilter::make('is_eu_vat_registered')
                    ->label(__('filament.customer_fiscal_data.filters.is_eu_vat')),
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
