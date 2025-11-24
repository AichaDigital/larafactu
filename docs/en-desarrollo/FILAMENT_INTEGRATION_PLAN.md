# 🎨 Plan de Integración Filament 4 - Larafactu v1.0

**Estrategia**: Híbrida "Opinado Ahora, Agnóstico Después"  
**Deadline**: Diciembre 15, 2025  
**Decisión**: ✅ APROBADA  
**Fecha**: 2025-11-24

---

## 🎯 **Objetivos**

### **Corto Plazo (v1.0 - Dic 15, 2025)**
- ✅ Integrar Filament 4 directamente en paquetes core
- ✅ Namespace modular preparado para extracción futura
- ✅ Dashboard funcional para empresa hosting española
- ✅ Cumplir deadline sin sacrificar features

### **Largo Plazo (v2.0 - Q1 2026)**
- 🔄 Extraer UI a plugins independientes (`*-filament`)
- 🔄 Mantener core 100% agnóstico
- 🔄 Permitir múltiples UI providers (Nova, custom, etc.)

---

## 📐 **Arquitectura Modular (v1.0)**

### **Estructura de Namespaces**

```
aichadigital/larabill/
├── src/
│   ├── Models/                    # ✅ Core (agnóstico)
│   ├── Services/                  # ✅ Core (agnóstico)
│   ├── Actions/                   # ✅ Core (agnóstico)
│   └── Filament/                  # 🎨 UI (temporal, modular)
│       ├── Resources/
│       │   ├── InvoiceResource.php
│       │   ├── CustomerResource.php
│       │   └── ArticleResource.php
│       ├── Pages/
│       │   ├── Dashboard.php
│       │   └── Reports/
│       ├── Widgets/
│       │   ├── MonthlyRevenueChart.php
│       │   └── PendingInvoicesTable.php
│       └── Clusters/              # Filament 4: Agrupación lógica
│           └── BillingCluster.php
```

**Beneficios de esta estructura**:
1. ✅ Namespace `Filament/` aislado → Fácil de mover
2. ✅ Core sin dependencias UI → Testeable independientemente
3. ✅ Migración futura: `git mv src/Filament/ ../larabill-filament/`

---

## 🔧 **Implementación Técnica**

### **1. ServiceProvider con Feature Flag**

```php
// larabill/src/LarabillServiceProvider.php

namespace AichaDigital\Larabill;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LarabillServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('larabill')
            ->hasConfigFile()
            ->hasMigrations($this->getMigrations())
            ->hasTranslations()
            ->hasViews(); // Para Filament views custom
    }

    public function packageBooted(): void
    {
        // ✅ Solo registrar Filament si está instalado
        if ($this->app->bound('filament')) {
            $this->registerFilamentResources();
        }
    }

    protected function registerFilamentResources(): void
    {
        // Auto-discovery de Resources
        $namespace = 'AichaDigital\\Larabill\\Filament\\Resources';
        
        if (config('larabill.filament.enabled', true)) {
            \Filament\Facades\Filament::serving(function () use ($namespace) {
                \Filament\Facades\Filament::registerResources([
                    "{$namespace}\\InvoiceResource",
                    "{$namespace}\\CustomerResource",
                    "{$namespace}\\ArticleResource",
                ]);
            });
        }
    }
}
```

### **2. Config con UI Flags**

```php
// larabill/config/larabill.php

return [
    /*
    |--------------------------------------------------------------------------
    | Filament UI Integration (v1.0 - Temporary)
    |--------------------------------------------------------------------------
    |
    | In v1.0, Filament UI is integrated directly in the core package for
    | faster development. In v2.0, this will be extracted to a separate
    | `larabill-filament` plugin package.
    |
    | Set 'enabled' to false to disable Filament resources registration.
    |
    */
    'filament' => [
        'enabled' => env('LARABILL_FILAMENT_ENABLED', true),
        
        'navigation' => [
            'group' => env('LARABILL_NAVIGATION_GROUP', 'Billing'),
            'sort' => env('LARABILL_NAVIGATION_SORT', 10),
        ],
        
        'resources' => [
            'invoice' => [
                'enabled' => true,
                'icon' => 'heroicon-o-document-text',
                'sort' => 1,
            ],
            'customer' => [
                'enabled' => true,
                'icon' => 'heroicon-o-users',
                'sort' => 2,
            ],
            'article' => [
                'enabled' => true,
                'icon' => 'heroicon-o-cube',
                'sort' => 3,
            ],
        ],
    ],
    
    // ... resto de config
];
```

### **3. Base Resource Abstract**

```php
// larabill/src/Filament/Resources/LarabillResource.php

namespace AichaDigital\Larabill\Filament\Resources;

use Filament\Resources\Resource;

abstract class LarabillResource extends Resource
{
    /**
     * Get navigation group from config.
     */
    public static function getNavigationGroup(): ?string
    {
        return config('larabill.filament.navigation.group', 'Billing');
    }
    
    /**
     * Get navigation sort order from config.
     */
    public static function getNavigationSort(): ?int
    {
        return config('larabill.filament.navigation.sort', 10);
    }
    
    /**
     * Helper: Format base100 to money string.
     */
    protected static function formatMoney(int $amount, string $currency = 'EUR'): string
    {
        return number_format($amount / 100, 2, ',', '.') . ' ' . $currency;
    }
}
```

---

## 📦 **Paquetes y Dependencias**

### **composer.json Updates**

```json
{
    "name": "aichadigital/larabill",
    "require": {
        "php": "^8.4",
        "illuminate/contracts": "^12.0",
        "aichadigital/lara100": "^1.1",
        "filament/filament": "^4.0",
        "spatie/laravel-package-tools": "^1.17"
    },
    "require-dev": {
        "pestphp/pest": "^4.0",
        "pestphp/pest-plugin-laravel": "^4.0"
    },
    "autoload": {
        "psr-4": {
            "AichaDigital\\Larabill\\": "src/",
            "AichaDigital\\Larabill\\Database\\Factories\\": "database/factories/",
            "AichaDigital\\Larabill\\Database\\Seeders\\": "database/seeders/"
        },
        "files": [
            "src/helpers.php"
        ]
    },
    "extra": {
        "laravel": {
            "providers": [
                "AichaDigital\\Larabill\\LarabillServiceProvider"
            ]
        }
    }
}
```

### **Nota Importante en README.md**

```markdown
## ⚠️ Filament Integration (v1.0 - Temporary)

**Current Status (v1.0)**: Filament 4 UI is integrated directly in this package.

**Future (v2.0+)**: The UI layer will be extracted to a separate `aichadigital/larabill-filament` 
plugin package, making the core package 100% UI-agnostic.

### Why this approach?

1. **Speed**: Deliver v1.0 by Dec 15, 2025 deadline
2. **Stability**: Test in production before architectural separation
3. **Modularity**: Namespace structure ready for trivial extraction

### Disabling Filament UI (if needed)

```php
// config/larabill.php
'filament' => [
    'enabled' => false, // Disable all Filament resources
],
```
```

---

## 🗓️ **Cronograma de Implementación**

### **Semana 1: Nov 25-29 (Setup Base)**

#### **Día 1-2: Larabill Base**
- [ ] Crear `src/Filament/Resources/LarabillResource.php` (abstract base)
- [ ] Actualizar `LarabillServiceProvider` con Filament registration
- [ ] Crear config `larabill.php` con flags
- [ ] Helper `formatMoney()` para base100

#### **Día 3-4: InvoiceResource**
- [ ] `InvoiceResource.php` con form completo
- [ ] Table con columnas base100 formateadas
- [ ] Actions: Calculate Totals, Finalize, Delete
- [ ] Filters: Status, ROI, Date Range
- [ ] Pages: List, Create, Edit, View

#### **Día 5: CustomerResource**
- [ ] `CustomerResource.php` con form
- [ ] Relación con `CustomerTaxProfile`
- [ ] Infolist para mostrar datos fiscales
- [ ] Actions: Archive, Export

---

### **Semana 2: Dic 2-6 (Resources Core)**

#### **Larabill (continúa)**
- [ ] `ArticleResource.php` (productos/servicios)
- [ ] `CustomerTaxProfileResource.php`
- [ ] `InvoiceItemResource.php` (si necesario, o nested en Invoice)

#### **Lara-Verifactu**
- [ ] Crear `src/Filament/` estructura
- [ ] `VerifactuInvoiceResource.php`
- [ ] `AeatDashboard.php` (Page custom)
- [ ] Widget: AEAT Status Badge
- [ ] Action: Send to AEAT

#### **Laratickets**
- [ ] Crear `src/Filament/` estructura
- [ ] `TicketResource.php`
- [ ] `TicketCategoryResource.php`
- [ ] Widget: Open Tickets Counter
- [ ] Filters: Priority, Status, Assigned

#### **LaraROI**
- [ ] Crear `src/Filament/` estructura
- [ ] `RoiQueryResource.php`
- [ ] `VatValidationWidget.php`
- [ ] Action: Verify VAT Number

---

### **Semana 3: Dic 9-13 (Dashboard & Widgets)**

#### **Larafactu Dashboard**
- [ ] `app/Filament/Pages/Dashboard.php` (override default)
- [ ] Widget: Monthly Revenue Chart (Line chart, base100 → EUR)
- [ ] Widget: Pending Invoices Table
- [ ] Widget: Recent Customers
- [ ] Widget: Hosting KPIs (domains, servers, tickets)

#### **Clusters (Filament 4)**
- [ ] `BillingCluster.php` (agrupa Invoice, Customer, Article)
- [ ] `ComplianceCluster.php` (agrupa Verifactu, ROI)
- [ ] `SupportCluster.php` (agrupa Tickets)

#### **Integración Between Packages**
- [ ] Desde `InvoiceResource`: Botón "Register with AEAT" (llama a Verifactu service)
- [ ] Desde `CustomerResource`: Ver tickets del cliente
- [ ] Desde `TicketResource`: Ver invoices del cliente

---

### **Semana 4: Dic 16-20 (Testing & Polish)**

#### **Browser Tests (Pest 4)**
- [ ] Test: Create invoice flow
- [ ] Test: Calculate totals action
- [ ] Test: Finalize invoice (immutability)
- [ ] Test: Register with AEAT
- [ ] Test: Create ticket from customer
- [ ] Test: Dashboard widgets load correctly

#### **Documentación**
- [ ] `docs/filament/RESOURCES.md` (uso de cada Resource)
- [ ] `docs/filament/CUSTOMIZATION.md` (cómo customizar)
- [ ] `docs/filament/MIGRATION_V2.md` (plan de extracción)
- [ ] Actualizar README principal

#### **Smoke Testing**
- [ ] Deploy a staging
- [ ] Crear 10 invoices reales
- [ ] Registrar con AEAT sandbox
- [ ] Crear 5 tickets
- [ ] Verificar widgets funcionan

---

## 🎨 **Ejemplo: InvoiceResource Completo**

```php
<?php

namespace AichaDigital\Larabill\Filament\Resources;

use AichaDigital\Larabill\Enums\InvoiceStatus;
use AichaDigital\Larabill\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends LarabillResource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('larabill::filament.invoice.header'))
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('fiscal_number')
                                    ->label(__('larabill::filament.invoice.fiscal_number'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText(__('larabill::filament.invoice.fiscal_number_help')),
                                
                                Forms\Components\Select::make('status')
                                    ->label(__('larabill::filament.invoice.status'))
                                    ->options(InvoiceStatus::class)
                                    ->required()
                                    ->default(InvoiceStatus::DRAFT),
                                
                                Forms\Components\Toggle::make('is_roi_taxed')
                                    ->label(__('larabill::filament.invoice.reverse_charge'))
                                    ->helperText(__('larabill::filament.invoice.reverse_charge_help'))
                                    ->reactive(),
                            ]),
                    ]),
                
                Forms\Components\Section::make(__('larabill::filament.invoice.customer'))
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'display_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('display_name')
                                    ->required(),
                                // ... más campos
                            ]),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('invoice_date')
                                    ->label(__('larabill::filament.invoice.invoice_date'))
                                    ->required()
                                    ->default(now()),
                                
                                Forms\Components\DatePicker::make('service_date')
                                    ->label(__('larabill::filament.invoice.service_date')),
                                
                                Forms\Components\DatePicker::make('due_date')
                                    ->label(__('larabill::filament.invoice.due_date'))
                                    ->default(fn () => now()->addDays(15)),
                            ]),
                    ]),
                
                Forms\Components\Section::make(__('larabill::filament.invoice.items'))
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('article_id')
                                    ->relationship('article', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($article = \AichaDigital\Larabill\Models\Article::find($state)) {
                                            $set('unit_price', $article->base_price);
                                            $set('description', $article->description);
                                        }
                                    }),
                                
                                Forms\Components\TextInput::make('description')
                                    ->required()
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(100)
                                    ->suffix('x100')
                                    ->helperText('1 = 100, 2.5 = 250')
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                        self::calculateItemTotals($set, $get)
                                    ),
                                
                                Forms\Components\TextInput::make('unit_price')
                                    ->numeric()
                                    ->suffix('€ x100')
                                    ->helperText('€10.00 = 1000')
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                        self::calculateItemTotals($set, $get)
                                    ),
                                
                                Forms\Components\TextInput::make('taxable_amount')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->suffix('€ x100'),
                                
                                Forms\Components\TextInput::make('tax_rate')
                                    ->numeric()
                                    ->default(2100)
                                    ->suffix('% x100')
                                    ->helperText('21% = 2100')
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => 
                                        self::calculateItemTotals($set, $get)
                                    ),
                                
                                Forms\Components\TextInput::make('total_tax_amount')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->suffix('€ x100'),
                                
                                Forms\Components\TextInput::make('total_amount')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->suffix('€ x100'),
                            ])
                            ->columns(8)
                            ->reorderable()
                            ->collapsible()
                            ->defaultItems(1)
                            ->addActionLabel(__('larabill::filament.invoice.add_item')),
                    ]),
                
                Forms\Components\Section::make(__('larabill::filament.invoice.totals'))
                    ->schema([
                        Forms\Components\Placeholder::make('calculated_totals')
                            ->label('')
                            ->content(function (Forms\Get $get) {
                                $items = collect($get('items') ?? []);
                                $taxable = $items->sum('taxable_amount');
                                $tax = $items->sum('total_tax_amount');
                                $total = $items->sum('total_amount');
                                
                                return new \Illuminate\Support\HtmlString("
                                    <div class='space-y-2 text-right'>
                                        <div class='text-sm text-gray-600 dark:text-gray-400'>
                                            <span class='font-medium'>Taxable:</span> " . self::formatMoney($taxable) . "
                                        </div>
                                        <div class='text-sm text-gray-600 dark:text-gray-400'>
                                            <span class='font-medium'>Tax:</span> " . self::formatMoney($tax) . "
                                        </div>
                                        <div class='text-lg font-bold text-gray-900 dark:text-white border-t pt-2'>
                                            <span class='font-medium'>Total:</span> " . self::formatMoney($total) . "
                                        </div>
                                    </div>
                                ");
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fiscal_number')
                    ->label(__('larabill::filament.invoice.fiscal_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('customer.display_name')
                    ->label(__('larabill::filament.invoice.customer'))
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('invoice_date')
                    ->label(__('larabill::filament.invoice.invoice_date'))
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label(__('larabill::filament.invoice.total'))
                    ->formatStateUsing(fn (int $state) => self::formatMoney($state))
                    ->sortable()
                    ->alignEnd(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('larabill::filament.invoice.status'))
                    ->formatStateUsing(fn (int $state) => InvoiceStatus::from($state)->getLabel())
                    ->colors([
                        'secondary' => InvoiceStatus::DRAFT->value,
                        'warning' => InvoiceStatus::SENT->value,
                        'success' => InvoiceStatus::PAID->value,
                        'danger' => InvoiceStatus::OVERDUE->value,
                    ]),
                
                Tables\Columns\IconColumn::make('is_roi_taxed')
                    ->label(__('larabill::filament.invoice.roi'))
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('heroicon-o-banknotes')
                    ->trueColor('info')
                    ->falseColor('success'),
                
                Tables\Columns\IconColumn::make('is_immutable')
                    ->label(__('larabill::filament.invoice.immutable'))
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(InvoiceStatus::class),
                
                Tables\Filters\TernaryFilter::make('is_roi_taxed')
                    ->label(__('larabill::filament.invoice.reverse_charge')),
                
                Tables\Filters\TernaryFilter::make('is_immutable')
                    ->label(__('larabill::filament.invoice.immutable')),
                
                Tables\Filters\Filter::make('invoice_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('invoice_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('invoice_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('calculate_totals')
                    ->label(__('larabill::filament.actions.calculate_totals'))
                    ->icon('heroicon-o-calculator')
                    ->action(function (Invoice $record) {
                        $record->calculateTotals()->save();
                        
                        \Filament\Notifications\Notification::make()
                            ->title(__('larabill::filament.notifications.totals_calculated'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->hidden(fn (Invoice $record) => $record->is_immutable),
                
                Tables\Actions\Action::make('finalize')
                    ->label(__('larabill::filament.actions.finalize'))
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->action(function (Invoice $record) {
                        $record->calculateTotals(); // Ensure totals are correct
                        $record->update([
                            'is_immutable' => true,
                            'immutable_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title(__('larabill::filament.notifications.invoice_finalized'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->hidden(fn (Invoice $record) => $record->is_immutable),
                
                Tables\Actions\Action::make('register_verifactu')
                    ->label(__('larabill::filament.actions.register_aeat'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->action(function (Invoice $record) {
                        $service = new \AichaDigital\Larabill\Services\InvoiceVerifactuService();
                        
                        try {
                            $verifactuInvoice = $service->registerInvoice($record);
                            
                            \Filament\Notifications\Notification::make()
                                ->title(__('larabill::filament.notifications.registered_with_aeat'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('larabill::filament.notifications.registration_failed'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Invoice $record) => $record->is_immutable),
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->hidden(fn (Invoice $record) => $record->is_immutable),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (Invoice $record) => $record->is_immutable),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('calculate_totals')
                        ->label(__('larabill::filament.actions.calculate_totals_bulk'))
                        ->icon('heroicon-o-calculator')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(fn (Invoice $invoice) => 
                                $invoice->calculateTotals()->save()
                            );
                        })
                        ->requiresConfirmation(),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('invoice_date', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(__('larabill::filament.invoice.details'))
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('fiscal_number'),
                                Infolists\Components\TextEntry::make('status')
                                    ->badge()
                                    ->formatStateUsing(fn (int $state) => InvoiceStatus::from($state)->getLabel()),
                                Infolists\Components\IconEntry::make('is_immutable')
                                    ->boolean(),
                            ]),
                        
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('invoice_date')
                                    ->date('d/m/Y'),
                                Infolists\Components\TextEntry::make('service_date')
                                    ->date('d/m/Y'),
                                Infolists\Components\TextEntry::make('due_date')
                                    ->date('d/m/Y'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make(__('larabill::filament.invoice.customer'))
                    ->schema([
                        Infolists\Components\TextEntry::make('customer.display_name'),
                        Infolists\Components\TextEntry::make('customer.currentTaxProfile.tax_code'),
                        Infolists\Components\TextEntry::make('customer.currentTaxProfile.country_code'),
                    ])
                    ->columns(3),
                
                Infolists\Components\Section::make(__('larabill::filament.invoice.items'))
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\TextEntry::make('description'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->formatStateUsing(fn (int $state) => $state / 100),
                                Infolists\Components\TextEntry::make('unit_price')
                                    ->formatStateUsing(fn (int $state) => self::formatMoney($state)),
                                Infolists\Components\TextEntry::make('total_amount')
                                    ->formatStateUsing(fn (int $state) => self::formatMoney($state)),
                            ])
                            ->columns(4),
                    ]),
                
                Infolists\Components\Section::make(__('larabill::filament.invoice.totals'))
                    ->schema([
                        Infolists\Components\TextEntry::make('taxable_amount')
                            ->formatStateUsing(fn (int $state) => self::formatMoney($state)),
                        Infolists\Components\TextEntry::make('total_tax_amount')
                            ->formatStateUsing(fn (int $state) => self::formatMoney($state)),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->formatStateUsing(fn (int $state) => self::formatMoney($state))
                            ->weight('bold')
                            ->size('lg'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }

    /**
     * Calculate item totals in real-time.
     */
    protected static function calculateItemTotals(Forms\Set $set, Forms\Get $get): void
    {
        $quantity = (int) ($get('quantity') ?? 100);
        $unitPrice = (int) ($get('unit_price') ?? 0);
        $taxRate = (int) ($get('tax_rate') ?? 0);
        
        // taxable_amount = (quantity * unit_price) / 100
        $taxableAmount = (int) round(($quantity * $unitPrice) / 100);
        
        // total_tax_amount = (taxable_amount * tax_rate) / 10000
        $totalTaxAmount = (int) round(($taxableAmount * $taxRate) / 10000);
        
        // total_amount = taxable_amount + total_tax_amount
        $totalAmount = $taxableAmount + $totalTaxAmount;
        
        $set('taxable_amount', $taxableAmount);
        $set('total_tax_amount', $totalTaxAmount);
        $set('total_amount', $totalAmount);
    }
}
```

---

## 🔄 **Plan de Migración a v2.0 (Post-Producción)**

### **Paso 1: Crear Paquete Plugin (Enero 2026)**

```bash
# Después de 2 meses en producción estable
cd /Users/abkrim/development/packages/aichadigital/
composer create-project vendor/package larabill-filament --stability=dev

cd larabill-filament
git init
```

### **Paso 2: Mover Namespace**

```bash
# Mover Filament resources del core al plugin
cd /Users/abkrim/development/packages/aichadigital/larabill
git mv src/Filament ../larabill-filament/src/
git commit -m "refactor: Extract Filament UI to separate plugin package"
```

### **Paso 3: Actualizar Dependencias**

```json
// larabill-filament/composer.json
{
    "name": "aichadigital/larabill-filament",
    "require": {
        "php": "^8.4",
        "aichadigital/larabill": "^1.0",
        "filament/filament": "^4.0"
    }
}
```

```json
// larabill/composer.json (v2.0)
{
    "require": {
        // ❌ REMOVE: "filament/filament": "^4.0"
    },
    "suggest": {
        "aichadigital/larabill-filament": "Filament 4 UI for Larabill"
    }
}
```

### **Paso 4: Deprecation Path**

```php
// larabill v1.5.0 (Febrero 2026 - Pre-release)
namespace AichaDigital\Larabill\Filament\Resources;

/**
 * @deprecated Use aichadigital/larabill-filament package instead
 * @see https://github.com/AichaDigital/larabill-filament
 */
class InvoiceResource extends Resource
{
    // Mantener por compatibilidad
}
```

### **Paso 5: Breaking Change en v2.0**

```markdown
# UPGRADE.md

## v1.x → v2.0

### ⚠️ Breaking Changes

**Filament UI Extracted**: The Filament UI layer has been moved to a separate package.

#### Before (v1.x)
```php
composer require aichadigital/larabill
// Filament resources included
```

#### After (v2.0)
```php
composer require aichadigital/larabill         # Core package (UI-agnostic)
composer require aichadigital/larabill-filament # Filament UI plugin
```

#### Migration Steps

1. Update `composer.json`:
```json
{
    "require": {
        "aichadigital/larabill": "^2.0",
        "aichadigital/larabill-filament": "^1.0"
    }
}
```

2. Run `composer update`

3. No code changes needed (namespace remains the same)

4. Profit! 🎉
```

---

## ✅ **Checklist de Entrega v1.0**

### **Core Features**
- [ ] InvoiceResource (CRUD + Actions)
- [ ] CustomerResource (CRUD + Tax Profiles)
- [ ] ArticleResource (CRUD)
- [ ] Dashboard con widgets
- [ ] Integración Verifactu (botón AEAT)
- [ ] ROI validation

### **Quality**
- [ ] Tests browser (Pest 4) para cada Resource
- [ ] Documentación Filament completa
- [ ] Translations (ES, EN)
- [ ] README con disclaimer v2.0

### **Deploy**
- [ ] Staging deployment exitoso
- [ ] 10 invoices de prueba creadas
- [ ] AEAT sandbox registro OK
- [ ] Performance OK (<500ms por página)

---

## 📚 **Recursos Adicionales**

- **Filament 4 Docs**: https://filamentphp.com/docs/4.x/
- **Pest Browser Testing**: https://pestphp.com/docs/browser-testing
- **Larabill Core**: `/packages/aichadigital/larabill/README.md`
- **Verifactu Integration**: `/docs/en-desarrollo/VERIFACTU_INTEGRATION_GUIDE.md`

---

**Última actualización**: 2025-11-24  
**Estado**: ✅ APROBADO - Listo para implementación  
**Próxima sesión**: Comenzar implementación InvoiceResource

