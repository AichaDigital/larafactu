<?php

declare(strict_types=1);

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

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.groups.billing');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.invoice.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.invoice.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.invoice.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('prefix')
                    ->label(__('filament.invoice.fields.prefix'))
                    ->default('FAC')
                    ->maxLength(10)
                    ->required()
                    ->columnSpan(1),

                Forms\Components\Select::make('serie')
                    ->label(__('filament.invoice.fields.serie'))
                    ->options(InvoiceSerieType::class)
                    ->enum(InvoiceSerieType::class)
                    ->default(InvoiceSerieType::INVOICE)
                    ->required()
                    ->columnSpan(1),

                Forms\Components\Select::make('status')
                    ->label(__('filament.invoice.fields.status'))
                    ->options(InvoiceStatus::class)
                    ->enum(InvoiceStatus::class)
                    ->default(InvoiceStatus::DRAFT)
                    ->required()
                    ->columnSpan(1),

                Forms\Components\DatePicker::make('invoice_date')
                    ->label(__('filament.invoice.fields.invoice_date'))
                    ->default(now())
                    ->required()
                    ->native(false)
                    ->columnSpan(1),

                Forms\Components\DatePicker::make('due_date')
                    ->label(__('filament.invoice.fields.due_date'))
                    ->default(now()->addDays(30))
                    ->native(false)
                    ->columnSpan(1),

                Forms\Components\Select::make('user_id')
                    ->label(__('filament.invoice.fields.user_id'))
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
                    ->label(__('filament.invoice.fields.notes'))
                    ->rows(3)
                    ->columnSpan(2),

                Forms\Components\TextInput::make('payment_terms')
                    ->label(__('filament.invoice.fields.payment_terms'))
                    ->maxLength(255)
                    ->columnSpan(1),

                Forms\Components\DateTimePicker::make('paid_at')
                    ->label(__('filament.invoice.fields.paid_at'))
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
                    ->label(__('filament.invoice.fields.fiscal_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('serie')
                    ->label(__('filament.invoice.fields.serie'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('filament.invoice.fields.status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label(__('filament.invoice.table.customer'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('invoice_date')
                    ->label(__('filament.invoice.fields.invoice_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label(__('filament.invoice.fields.total_amount'))
                    ->money('EUR', locale: 'es')
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->money('EUR', locale: 'es'),
                    ]),

                TextColumn::make('due_date')
                    ->label(__('filament.invoice.fields.due_date'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('paid_at')
                    ->label(__('filament.invoice.fields.paid_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder(__('filament.invoice.table.paid_placeholder')),

                TextColumn::make('created_at')
                    ->label(__('filament.invoice.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.invoice.filters.status'))
                    ->options(InvoiceStatus::class)
                    ->multiple(),

                SelectFilter::make('serie')
                    ->label(__('filament.invoice.filters.serie'))
                    ->options(InvoiceSerieType::class)
                    ->multiple(),

                Filter::make('invoice_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label(__('filament.invoice.filters.from'))
                            ->native(false),
                        Forms\Components\DatePicker::make('until')
                            ->label(__('filament.invoice.filters.until'))
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
                    ->label(__('filament.invoice.filters.paid'))
                    ->placeholder(__('filament.invoice.filters.paid_all'))
                    ->trueLabel(__('filament.invoice.filters.paid_true'))
                    ->falseLabel(__('filament.invoice.filters.paid_false'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('paid_at'),
                        false: fn (Builder $query) => $query->whereNull('paid_at'),
                    ),
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
        try {
            $count = static::getModel()::where('status', InvoiceStatus::DRAFT)->count();

            return $count > 0 ? (string) $count : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
