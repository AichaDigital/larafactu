<?php

namespace App\Filament\Resources\FiscalSettings;

use AichaDigital\Larabill\Models\FiscalSettings;
use App\Filament\Resources\FiscalSettings\Pages\ManageFiscalSettings;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FiscalSettingResource extends Resource
{
    protected static ?string $model = FiscalSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog;

    protected static ?string $navigationLabel = 'Fiscal Settings';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),

                TextInput::make('fiscal_year')
                    ->required()
                    ->numeric()
                    ->default(now()->year),

                Toggle::make('is_oss')
                    ->label('OSS/MOSS Enabled')
                    ->helperText('One Stop Shop / Mini One Stop Shop for EU VAT'),

                Toggle::make('is_roi')
                    ->label('ROI Enabled')
                    ->helperText('Reverse Charge Operator Identification'),

                TextInput::make('destination_vat_threshold')
                    ->label('Destination VAT Threshold')
                    ->numeric()
                    ->default(10000)
                    ->helperText('Threshold for destination VAT (€)'),

                Toggle::make('apply_destination_iva')
                    ->label('Apply Destination VAT'),

                TextInput::make('total_eu_sales')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->helperText('Total EU sales (calculated)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fiscal_year')
                    ->sortable(),

                IconColumn::make('is_oss')
                    ->label('OSS')
                    ->boolean(),

                IconColumn::make('is_roi')
                    ->label('ROI')
                    ->boolean(),

                TextColumn::make('destination_vat_threshold')
                    ->label('VAT Threshold')
                    ->money('EUR', locale: 'es')
                    ->sortable(),

                IconColumn::make('apply_destination_iva')
                    ->label('Destination VAT')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('total_eu_sales')
                    ->money('EUR', locale: 'es')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('fiscal_year')
                    ->options(fn () => collect(range(now()->year - 3, now()->year + 1))
                        ->mapWithKeys(fn ($year) => [$year => $year])),

                SelectFilter::make('is_oss')
                    ->label('OSS Enabled')
                    ->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ]),

                SelectFilter::make('is_roi')
                    ->label('ROI Enabled')
                    ->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fiscal_year', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFiscalSettings::route('/'),
        ];
    }
}
