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

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('filament.company_fiscal_config.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.company_fiscal_config.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.company_fiscal_config.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('business_name')
                    ->label(__('filament.company_fiscal_config.fields.business_name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('tax_id')
                    ->label(__('filament.company_fiscal_config.fields.tax_id'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('filament.company_fiscal_config.fields.tax_id_placeholder')),

                Forms\Components\TextInput::make('legal_entity_type')
                    ->label(__('filament.company_fiscal_config.fields.legal_entity_type'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('filament.company_fiscal_config.fields.legal_entity_placeholder')),

                Forms\Components\TextInput::make('address')
                    ->label(__('filament.company_fiscal_config.fields.address'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('city')
                    ->label(__('filament.company_fiscal_config.fields.city'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('state')
                    ->label(__('filament.company_fiscal_config.fields.state'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('zip_code')
                    ->label(__('filament.company_fiscal_config.fields.zip_code'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('country_code')
                    ->label(__('filament.company_fiscal_config.fields.country_code'))
                    ->required()
                    ->maxLength(2)
                    ->default('ES')
                    ->placeholder(__('filament.company_fiscal_config.fields.country_placeholder')),

                Forms\Components\Toggle::make('is_oss')
                    ->label(__('filament.company_fiscal_config.fields.is_oss'))
                    ->helperText(__('filament.company_fiscal_config.fields.is_oss_helper')),

                Forms\Components\Toggle::make('is_roi')
                    ->label(__('filament.company_fiscal_config.fields.is_roi'))
                    ->helperText(__('filament.company_fiscal_config.fields.is_roi_helper')),

                Forms\Components\TextInput::make('currency')
                    ->label(__('filament.company_fiscal_config.fields.currency'))
                    ->required()
                    ->maxLength(3)
                    ->default('EUR'),

                Forms\Components\TextInput::make('fiscal_year_start')
                    ->label(__('filament.company_fiscal_config.fields.fiscal_year_start'))
                    ->required()
                    ->maxLength(5)
                    ->default('01-01')
                    ->placeholder(__('filament.company_fiscal_config.fields.fiscal_year_placeholder')),

                Forms\Components\DatePicker::make('valid_from')
                    ->label(__('filament.company_fiscal_config.fields.valid_from'))
                    ->required()
                    ->default(now())
                    ->helperText(__('filament.company_fiscal_config.fields.valid_from_helper')),

                Forms\Components\DatePicker::make('valid_until')
                    ->label(__('filament.company_fiscal_config.fields.valid_until'))
                    ->helperText(__('filament.company_fiscal_config.fields.valid_until_helper')),

                Forms\Components\Toggle::make('is_active')
                    ->label(__('filament.company_fiscal_config.fields.is_active'))
                    ->default(true)
                    ->helperText(__('filament.company_fiscal_config.fields.is_active_helper')),

                Forms\Components\Textarea::make('notes')
                    ->label(__('filament.company_fiscal_config.fields.notes'))
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder(__('filament.company_fiscal_config.fields.notes_placeholder')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('business_name')
                    ->label(__('filament.company_fiscal_config.fields.business_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tax_id')
                    ->label(__('filament.company_fiscal_config.fields.tax_id'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_from')
                    ->label(__('filament.company_fiscal_config.fields.valid_from'))
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label(__('filament.company_fiscal_config.fields.valid_until'))
                    ->date('d/m/Y')
                    ->placeholder(__('filament.company_fiscal_config.table.valid_until_placeholder'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filament.company_fiscal_config.fields.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_oss')
                    ->label(__('filament.company_fiscal_config.fields.is_oss'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_roi')
                    ->label(__('filament.company_fiscal_config.fields.is_roi'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.company_fiscal_config.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('filament.company_fiscal_config.filters.is_active'))
                    ->placeholder(__('filament.company_fiscal_config.filters.is_active_all'))
                    ->trueLabel(__('filament.company_fiscal_config.filters.is_active_true'))
                    ->falseLabel(__('filament.company_fiscal_config.filters.is_active_false')),

                Tables\Filters\TernaryFilter::make('is_oss')
                    ->label(__('filament.company_fiscal_config.filters.is_oss')),

                Tables\Filters\TernaryFilter::make('is_roi')
                    ->label(__('filament.company_fiscal_config.filters.is_roi')),
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
