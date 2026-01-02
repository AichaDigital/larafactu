<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use AichaDigital\Larabill\Enums\InvoiceSerieType;
use AichaDigital\Larabill\Enums\InvoiceStatus;
use AichaDigital\Larabill\Models\Invoice;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Nueva Factura')]
class InvoiceCreate extends Component
{
    // Invoice fields
    public int $serie = 1; // INVOICE by default

    public string $invoiceDate = '';

    public string $dueDate = '';

    public string $serviceDate = '';

    public string $notes = '';

    public string $paymentTerms = '';

    // Customer
    public string $customerId = '';

    public string $customerSearch = '';

    // Items
    public array $items = [];

    // Calculated totals (in cents)
    public int $taxableAmount = 0;

    public int $totalTaxAmount = 0;

    public int $totalAmount = 0;

    public function mount(): void
    {
        $this->invoiceDate = now()->format('Y-m-d');
        $this->dueDate = now()->addDays(30)->format('Y-m-d');
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0, // in cents
            'tax_rate' => 21,
            'discount' => 0,
            'taxable_amount' => 0,
            'total_tax_amount' => 0,
            'total_amount' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            $this->calculateTotals();
        }
    }

    public function updatedItems(): void
    {
        $this->calculateTotals();
    }

    protected function calculateTotals(): void
    {
        $this->taxableAmount = 0;
        $this->totalTaxAmount = 0;
        $this->totalAmount = 0;

        foreach ($this->items as $index => $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitPrice = (int) ($item['unit_price'] ?? 0);
            $taxRate = (float) ($item['tax_rate'] ?? 21);
            $discount = (float) ($item['discount'] ?? 0);

            // Calculate line taxable amount
            $lineTaxable = (int) ($quantity * $unitPrice);

            // Apply discount
            if ($discount > 0) {
                $lineTaxable = (int) ($lineTaxable * (1 - $discount / 100));
            }

            // Calculate tax
            $lineTax = (int) ($lineTaxable * $taxRate / 100);
            $lineTotal = $lineTaxable + $lineTax;

            // Update item calculations
            $this->items[$index]['taxable_amount'] = $lineTaxable;
            $this->items[$index]['total_tax_amount'] = $lineTax;
            $this->items[$index]['total_amount'] = $lineTotal;

            // Accumulate totals
            $this->taxableAmount += $lineTaxable;
            $this->totalTaxAmount += $lineTax;
            $this->totalAmount += $lineTotal;
        }
    }

    public function rules(): array
    {
        return [
            'serie' => 'required|integer|in:0,1,2,3',
            'invoiceDate' => 'required|date',
            'dueDate' => 'nullable|date',
            'serviceDate' => 'nullable|date',
            'customerId' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'paymentTerms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|integer|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'customerId.required' => 'Debes seleccionar un cliente.',
            'items.required' => 'Debe haber al menos una linea.',
            'items.*.description.required' => 'La descripcion es obligatoria.',
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.unit_price.required' => 'El precio es obligatorio.',
        ];
    }

    public function save(): void
    {
        $this->calculateTotals();
        $this->validate();

        DB::transaction(function () {
            $serieType = InvoiceSerieType::from($this->serie);
            $fiscalYear = (int) date('Y', strtotime($this->invoiceDate));

            // Get next series number
            $seriesNumber = $this->getNextSeriesNumber($serieType, $fiscalYear);

            // Generate fiscal number
            $fiscalNumber = $this->generateFiscalNumber($serieType, $fiscalYear, $seriesNumber);

            // Create invoice
            $invoice = Invoice::create([
                'id' => (string) Str::uuid7(),
                'fiscal_number' => $fiscalNumber,
                'prefix' => $serieType->defaultPrefix(),
                'serie' => $serieType,
                'series_number' => $seriesNumber,
                'fiscal_year' => $fiscalYear,
                'invoice_date' => $this->invoiceDate,
                'issued_at' => now(),
                'service_date' => $this->serviceDate ?: null,
                'due_date' => $this->dueDate ?: null,
                'status' => InvoiceStatus::DRAFT,
                'user_id' => auth()->id(),
                'billable_user_id' => $this->customerId,
                'taxable_amount' => $this->taxableAmount,
                'total_tax_amount' => $this->totalTaxAmount,
                'total_amount' => $this->totalAmount,
                'notes' => $this->notes ?: null,
                'payment_terms' => $this->paymentTerms ?: null,
            ]);

            // Create invoice items
            foreach ($this->items as $index => $item) {
                DB::table('invoice_items')->insert([
                    'id' => (string) Str::uuid7(),
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'],
                    'discount' => $item['discount'] ?? 0,
                    'taxable_amount' => $item['taxable_amount'],
                    'total_tax_amount' => $item['total_tax_amount'],
                    'total_amount' => $item['total_amount'],
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        session()->flash('success', 'Factura creada correctamente.');

        $this->redirect(route('invoices.index'), navigate: true);
    }

    protected function getNextSeriesNumber(InvoiceSerieType $serie, int $fiscalYear): int
    {
        $lastNumber = DB::table('invoices')
            ->where('serie', $serie->value)
            ->where('fiscal_year', $fiscalYear)
            ->max('series_number');

        return ($lastNumber ?? 0) + 1;
    }

    protected function generateFiscalNumber(InvoiceSerieType $serie, int $fiscalYear, int $seriesNumber): string
    {
        $prefix = $serie->defaultPrefix();

        return sprintf('%s-%d-%06d', $prefix, $fiscalYear, $seriesNumber);
    }

    public function getCustomers(): array
    {
        $query = User::query();

        if ($this->customerSearch) {
            $search = $this->customerSearch;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->limit(10)->get()->toArray();
    }

    public function selectCustomer(string $id): void
    {
        $this->customerId = $id;
        $this->customerSearch = '';
    }

    public function getSelectedCustomer(): ?User
    {
        if (! $this->customerId) {
            return null;
        }

        return User::find($this->customerId);
    }

    public function render(): View
    {
        return view('livewire.invoices.invoice-create', [
            'customers' => $this->getCustomers(),
            'selectedCustomer' => $this->getSelectedCustomer(),
            'serieTypes' => [
                0 => 'Proforma',
                1 => 'Factura',
                2 => 'Simplificada',
                3 => 'Rectificativa',
            ],
        ]);
    }
}
