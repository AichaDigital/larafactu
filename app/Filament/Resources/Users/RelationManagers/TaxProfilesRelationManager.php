<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use AichaDigital\Larabill\Models\LegalEntityType;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TaxProfilesRelationManager extends RelationManager
{
    protected static string $relationship = 'taxProfiles';

    protected static ?string $recordTitleAttribute = 'fiscal_name';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.user.relations.tax_profiles');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Identity
                Forms\Components\TextInput::make('fiscal_name')
                    ->label(__('filament.user_tax_profile.fields.fiscal_name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('tax_id')
                    ->label(__('filament.user_tax_profile.fields.tax_id'))
                    ->maxLength(50)
                    ->placeholder('12345678A')
                    ->columnSpan(1),

                Forms\Components\Select::make('legal_entity_type_code')
                    ->label(__('filament.user_tax_profile.fields.legal_entity_type'))
                    ->options(
                        LegalEntityType::active()
                            ->ordered()
                            ->get()
                            ->mapWithKeys(fn (LegalEntityType $type) => [
                                $type->code => $type->formatted_name,
                            ])
                    )
                    ->searchable()
                    ->preload()
                    ->columnSpan(1),

                Forms\Components\Toggle::make('is_company')
                    ->label(__('filament.user_tax_profile.fields.is_company'))
                    ->default(false)
                    ->columnSpan(1),

                // Address
                Forms\Components\TextInput::make('address')
                    ->label(__('filament.user_tax_profile.fields.address'))
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('city')
                    ->label(__('filament.user_tax_profile.fields.city'))
                    ->maxLength(100)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('state')
                    ->label(__('filament.user_tax_profile.fields.state'))
                    ->maxLength(100)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('zip_code')
                    ->label(__('filament.user_tax_profile.fields.zip_code'))
                    ->maxLength(20)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('country_code')
                    ->label(__('filament.user_tax_profile.fields.country_code'))
                    ->default('ES')
                    ->maxLength(2)
                    ->required()
                    ->columnSpan(1),

                // Fiscal
                Forms\Components\Toggle::make('is_eu_vat_registered')
                    ->label(__('filament.user_tax_profile.fields.is_eu_vat_registered'))
                    ->helperText(__('filament.user_tax_profile.fields.is_eu_vat_helper'))
                    ->default(false)
                    ->columnSpan(1),

                Forms\Components\Toggle::make('is_exempt_vat')
                    ->label(__('filament.user_tax_profile.fields.is_exempt_vat'))
                    ->helperText(__('filament.user_tax_profile.fields.is_exempt_helper'))
                    ->default(false)
                    ->columnSpan(1),

                // Validity
                Forms\Components\DatePicker::make('valid_from')
                    ->label(__('filament.user_tax_profile.fields.valid_from'))
                    ->default(now())
                    ->required()
                    ->native(false)
                    ->columnSpan(1),

                Forms\Components\DatePicker::make('valid_until')
                    ->label(__('filament.user_tax_profile.fields.valid_until'))
                    ->helperText(__('filament.user_tax_profile.fields.valid_until_helper'))
                    ->native(false)
                    ->columnSpan(1),

                Forms\Components\Toggle::make('is_active')
                    ->label(__('filament.user_tax_profile.fields.is_active'))
                    ->default(true)
                    ->columnSpan(1),

                Forms\Components\Textarea::make('notes')
                    ->label(__('filament.user_tax_profile.fields.notes'))
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fiscal_name')
                    ->label(__('filament.user_tax_profile.fields.fiscal_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tax_id')
                    ->label(__('filament.user_tax_profile.fields.tax_id'))
                    ->placeholder('-')
                    ->copyable(),

                IconColumn::make('is_company')
                    ->label(__('filament.user_tax_profile.fields.is_company'))
                    ->boolean(),

                TextColumn::make('country_code')
                    ->label(__('filament.user_tax_profile.fields.country_code'))
                    ->badge(),

                TextColumn::make('valid_from')
                    ->label(__('filament.user_tax_profile.fields.valid_from'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->label(__('filament.user_tax_profile.fields.valid_until'))
                    ->date('d/m/Y')
                    ->placeholder(__('filament.user_tax_profile.table.current'))
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('filament.user_tax_profile.fields.is_active'))
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('filament.user_tax_profile.filters.is_active')),

                TernaryFilter::make('is_company')
                    ->label(__('filament.user_tax_profile.filters.is_company')),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('valid_from', 'desc');
    }
}
