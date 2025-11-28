<?php

namespace App\Filament\Resources\Invoices;

use AichaDigital\Larabill\Enums\InvoiceSerieType;
use AichaDigital\Larabill\Enums\InvoiceStatus;
use AichaDigital\Larabill\Models\Invoice;
use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Resources\Invoices\RelationManagers\InvoiceItemsRelationManager;
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
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Facturas';

    protected static ?string $modelLabel = 'Factura';

    protected static ?string $pluralModelLabel = 'Facturas';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('prefix')
                    ->label('Prefijo')
                    ->default('FAC')
                    ->maxLength(10)
                    ->required()
                    ->columnSpan(1),

                Forms\Components\Select::make('serie')
                    ->label('Serie')
                    ->options(InvoiceSerieType::class)
                    ->enum(InvoiceSerieType::class)
                    ->default(InvoiceSerieType::INVOICE)
                    ->required()
                    ->columnSpan(1),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options(InvoiceStatus::class)
                    ->enum(InvoiceStatus::class)
                    ->default(InvoiceStatus::DRAFT)
                    ->required()
                    ->columnSpan(1),

                Forms\Components\DatePicker::make('invoice_date')
                    ->label('Fecha Factura')
                    ->default(now())
                    ->required()
                    ->native(false)
                    ->columnSpan(1),

                Forms\Components\DatePicker::make('due_date')
                    ->label('Fecha Vencimiento')
                    ->default(now()->addDays(30))
                    ->native(false)
                    ->columnSpan(1),

                Forms\Components\Select::make('user_id')
                    ->label('Usuario/Cliente')
                    ->relationship('user', 'name')
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required()
                    ->columnSpan(2)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(User::class),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->minLength(8),
                    ]),

                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->columnSpan(2),

                Forms\Components\TextInput::make('payment_terms')
                    ->label('Condiciones de Pago')
                    ->maxLength(255)
                    ->columnSpan(1),

                Forms\Components\DateTimePicker::make('paid_at')
                    ->label('Fecha de Pago')
                    ->native(false)
                    ->columnSpan(1),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fiscal_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('serie')
                    ->label('Serie')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('invoice_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('EUR', locale: 'es')
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->money('EUR', locale: 'es'),
                    ]),

                TextColumn::make('due_date')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('paid_at')
                    ->label('Pagado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Pendiente'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(InvoiceStatus::class)
                    ->multiple(),

                SelectFilter::make('serie')
                    ->label('Serie')
                    ->options(InvoiceSerieType::class)
                    ->multiple(),

                Filter::make('invoice_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Desde')
                            ->native(false),
                        Forms\Components\DatePicker::make('until')
                            ->label('Hasta')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '<=', $date),
                            );
                    }),

                TernaryFilter::make('paid')
                    ->label('Pagado')
                    ->placeholder('Todos')
                    ->trueLabel('Pagadas')
                    ->falseLabel('Pendientes')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('paid_at'),
                        false: fn (Builder $query) => $query->whereNull('paid_at'),
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->striped()
            ->persistFiltersInSession()
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            InvoiceItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'view' => ViewInvoice::route('/{record}'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', InvoiceStatus::DRAFT)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
