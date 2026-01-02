<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use AichaDigital\Larabill\Enums\InvoiceSerieType;
use AichaDigital\Larabill\Enums\InvoiceStatus;
use AichaDigital\Larabill\Models\Invoice;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Facturas')]
class InvoiceList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $serie = '';

    #[Url]
    public string $year = '';

    public int $perPage = 15;

    public function mount(): void
    {
        if (empty($this->year)) {
            $this->year = (string) now()->year;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSerie(): void
    {
        $this->resetPage();
    }

    public function updatingYear(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $query = Invoice::query()
            ->with(['billableUser']);

        // Search filter
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('fiscal_number', 'like', "%{$search}%")
                    ->orWhereHas('billableUser', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($this->status !== '') {
            $query->where('status', (int) $this->status);
        }

        // Serie filter
        if ($this->serie !== '') {
            $query->where('serie', (int) $this->serie);
        }

        // Year filter
        if ($this->year !== '') {
            $query->where('fiscal_year', (int) $this->year);
        }

        $invoices = $query
            ->orderBy('invoice_date', 'desc')
            ->orderBy('series_number', 'desc')
            ->paginate($this->perPage);

        // Get available years for filter
        $years = DB::table('invoices')
            ->selectRaw('DISTINCT fiscal_year')
            ->orderBy('fiscal_year', 'desc')
            ->pluck('fiscal_year')
            ->toArray();

        if (empty($years)) {
            $years = [now()->year];
        }

        return view('livewire.invoices.invoice-list', [
            'invoices' => $invoices,
            'statuses' => InvoiceStatus::cases(),
            'series' => InvoiceSerieType::cases(),
            'years' => $years,
        ]);
    }
}
