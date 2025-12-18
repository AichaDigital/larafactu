<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use AichaDigital\Larabill\Enums\ItemType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoiceItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('filament.invoice_items.title');
    }

    public static function getModelLabel(): string
    {
        return __('filament.invoice_items.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.invoice_items.plural_model_label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('item_type')
                    ->label(__('filament.invoice_items.fields.item_type'))
                    ->options(ItemType::class)
                    ->enum(ItemType::class)
                    ->default(ItemType::GOOD)
                    ->required()
                    ->columnSpan(1),

                Forms\Components\TextInput::make('quantity')
                    ->label(__('filament.invoice_items.fields.quantity'))
                    ->numeric()
                    ->default(1.0)
                    ->required()
                    ->suffix('ud')
                    ->columnSpan(1),

                Forms\Components\Textarea::make('description')
                    ->label(__('filament.invoice_items.fields.description'))
                    ->required()
                    ->rows(2)
                    ->columnSpan(2),

                Forms\Components\TextInput::make('unit_price')
                    ->label(__('filament.invoice_items.fields.unit_price'))
                    ->numeric()
                    ->required()
                    ->prefix('€')
                    ->step(0.01)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('taxable_amount')
                    ->label(__('filament.invoice_items.fields.taxable_amount'))
                    ->numeric()
                    ->required()
                    ->prefix('€')
                    ->step(0.01)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('total_tax_amount')
                    ->label(__('filament.invoice_items.fields.total_tax_amount'))
                    ->numeric()
                    ->required()
                    ->prefix('€')
                    ->step(0.01)
                    ->default(0)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('total_amount')
                    ->label(__('filament.invoice_items.fields.total_amount'))
                    ->numeric()
                    ->required()
                    ->prefix('€')
                    ->step(0.01)
                    ->columnSpan(1),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('item_type')
                    ->label(__('filament.invoice_items.fields.item_type'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__('filament.invoice_items.fields.description'))
                    ->searchable()
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('quantity')
                    ->label(__('filament.invoice_items.fields.quantity'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('unit_price')
                    ->label(__('filament.invoice_items.table.price'))
                    ->money('EUR', locale: 'es')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('taxable_amount')
                    ->label(__('filament.invoice_items.table.base'))
                    ->money('EUR', locale: 'es')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('total_tax_amount')
                    ->label(__('filament.invoice_items.fields.total_tax_amount'))
                    ->money('EUR', locale: 'es')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('total_amount')
                    ->label(__('filament.invoice_items.fields.total_amount'))
                    ->money('EUR', locale: 'es')
                    ->sortable()
                    ->weight('bold')
                    ->alignEnd()
                    ->summarize([
                        Sum::make()
                            ->money('EUR', locale: 'es'),
                    ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        // Calcular totales automáticamente si no se proporcionan
                        if (! isset($data['taxable_amount'])) {
                            $data['taxable_amount'] = $data['quantity'] * $data['unit_price'];
                        }

                        if (! isset($data['total_amount'])) {
                            $data['total_amount'] = $data['taxable_amount'] + ($data['total_tax_amount'] ?? 0);
                        }

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('id', 'asc')
            ->reorderable('id');
    }
}
