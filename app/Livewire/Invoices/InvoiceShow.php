<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use AichaDigital\Larabill\Models\Invoice;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Ver Factura')]
class InvoiceShow extends Component
{
    public Invoice $invoice;

    public ?object $customerProfile = null;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load(['billableUser', 'items']);

        // Load customer tax profile if available
        if ($this->invoice->billableUser) {
            $this->customerProfile = DB::table('user_tax_profiles')
                ->where('user_id', $this->invoice->billableUser->id)
                ->where('is_active', true)
                ->whereNull('valid_until')
                ->first();
        }
    }

    public function getSerieLabel(): string
    {
        return match ($this->invoice->serie) {
            0 => 'Proforma',
            1 => 'Factura',
            2 => 'Simplificada',
            3 => 'Rectificativa',
            default => 'Desconocido',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->invoice->status) {
            0 => 'Borrador',
            1 => 'Enviada',
            2 => 'Pagada',
            3 => 'Vencida',
            4 => 'Anulada',
            5 => 'Pendiente',
            6 => 'Convertida',
            default => 'Desconocido',
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->invoice->status) {
            0 => 'badge-ghost',
            1 => 'badge-info',
            2 => 'badge-success',
            3 => 'badge-error',
            4 => 'badge-neutral',
            5 => 'badge-warning',
            6 => 'badge-secondary',
            default => 'badge-ghost',
        };
    }

    public function canEdit(): bool
    {
        return $this->invoice->status === 0; // Only DRAFT
    }

    public function render(): View
    {
        return view('livewire.invoices.invoice-show');
    }
}
