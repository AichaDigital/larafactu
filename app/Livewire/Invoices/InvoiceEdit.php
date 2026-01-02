<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use AichaDigital\Larabill\Models\Invoice;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Editar Factura')]
class InvoiceEdit extends Component
{
    public Invoice $invoice;

    // Invoice fields
    public int $serie = 1;

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

    public function mount(Invoice $invoice): void
    {
        // Only allow editing DRAFT invoices
        if ($invoice->status !== 0) {
            session()->flash('error', 'Solo se pueden editar facturas en borrador.');
            $this->redirect(route('invoices.show', $invoice), navigate: true);

            return;
        }

        $this->invoice = $invoice->load('items');

        // Load invoice data
        $this->serie = $invoice->serie->value ?? $invoice->serie;
        $this->invoiceDate = $invoice->invoice_date->format('Y-m-d');
        $this->dueDate = $invoice->due_date?->format('Y-m-d') ?? '';
        $this->serviceDate = $invoice->service_date?->format('Y-m-d') ?? '';
        $this->notes = $invoice->notes ?? '';
        $this->paymentTerms = $invoice->payment_terms ?? '';
        $this->customerId = $invoice->billable_user_id ?? '';

        // Load items
        $this->items = [];
        foreach ($invoice->items as $item) {
            $this->items[] = [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'tax_rate' => $item->tax_rate ?? 21,
                'discount' => $item->discount ?? 0,
                'taxable_amount' => $item->taxable_amount ?? 0,
                'total_tax_amount' => $item->total_tax_amount ?? 0,
                'total_amount' => $item->total_amount ?? 0,
            ];
        }

        // If no items, add one
        if (empty($this->items)) {
            $this->addItem();
        }

        // Set totals
        $this->taxableAmount = $invoice->taxable_amount ?? 0;
        $this->totalTaxAmount = $invoice->total_tax_amount ?? 0;
        $this->totalAmount = $invoice->total_amount ?? 0;
    }

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
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

            $lineTaxable = (int) ($quantity * $unitPrice);

            if ($discount > 0) {
                $lineTaxable = (int) ($lineTaxable * (1 - $discount / 100));
            }

            $lineTax = (int) ($lineTaxable * $taxRate / 100);
            $lineTotal = $lineTaxable + $lineTax;

            $this->items[$index]['taxable_amount'] = $lineTaxable;
            $this->items[$index]['total_tax_amount'] = $lineTax;
            $this->items[$index]['total_amount'] = $lineTotal;

            $this->taxableAmount += $lineTaxable;
            $this->totalTaxAmount += $lineTax;
            $this->totalAmount += $lineTotal;
        }
    }

    public function rules(): array
    {
        return [
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
            // Update invoice
            $this->invoice->update([
                'invoice_date' => $this->invoiceDate,
                'service_date' => $this->serviceDate ?: null,
                'due_date' => $this->dueDate ?: null,
                'billable_user_id' => $this->customerId,
                'taxable_amount' => $this->taxableAmount,
                'total_tax_amount' => $this->totalTaxAmount,
                'total_amount' => $this->totalAmount,
                'notes' => $this->notes ?: null,
                'payment_terms' => $this->paymentTerms ?: null,
            ]);

            // Get existing item IDs
            $existingIds = collect($this->items)
                ->pluck('id')
                ->filter()
                ->toArray();

            // Delete removed items
            DB::table('invoice_items')
                ->where('invoice_id', $this->invoice->id)
                ->when(! empty($existingIds), function ($query) use ($existingIds) {
                    $query->whereNotIn('id', $existingIds);
                })
                ->delete();

            // Update or create items
            foreach ($this->items as $index => $item) {
                if (! empty($item['id'])) {
                    // Update existing
                    DB::table('invoice_items')
                        ->where('id', $item['id'])
                        ->update([
                            'description' => $item['description'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'tax_rate' => $item['tax_rate'],
                            'discount' => $item['discount'] ?? 0,
                            'taxable_amount' => $item['taxable_amount'],
                            'total_tax_amount' => $item['total_tax_amount'],
                            'total_amount' => $item['total_amount'],
                            'sort_order' => $index,
                            'updated_at' => now(),
                        ]);
                } else {
                    // Create new
                    DB::table('invoice_items')->insert([
                        'id' => (string) Str::uuid7(),
                        'invoice_id' => $this->invoice->id,
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
            }
        });

        session()->flash('success', 'Factura actualizada correctamente.');

        $this->redirect(route('invoices.show', $this->invoice), navigate: true);
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

    public function getSerieLabel(): string
    {
        return match ($this->serie) {
            0 => 'Proforma',
            1 => 'Factura',
            2 => 'Simplificada',
            3 => 'Rectificativa',
            default => 'Desconocido',
        };
    }

    public function render(): View
    {
        return view('livewire.invoices.invoice-edit', [
            'customers' => $this->getCustomers(),
            'selectedCustomer' => $this->getSelectedCustomer(),
        ]);
    }
}
