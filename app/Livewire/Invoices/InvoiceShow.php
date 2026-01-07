<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use AichaDigital\Larabill\Enums\InvoiceSerieType;
use AichaDigital\Larabill\Enums\InvoiceStatus;
use AichaDigital\Larabill\Models\Invoice;
use AichaDigital\Larabill\Models\UserTaxProfile;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Ver Factura')]
class InvoiceShow extends Component
{
    public Invoice $invoice;

    public ?UserTaxProfile $customerProfile = null;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load(['billableUser', 'items']);

        // Load customer tax profile if available (ADR-004: owner_user_id)
        if ($this->invoice->billableUser) {
            $this->customerProfile = UserTaxProfile::where('owner_user_id', $this->invoice->billableUser->id)
                ->where('is_active', true)
                ->whereNull('valid_until')
                ->first();
        }
    }

    public function getSerieLabel(): string
    {
        return match ($this->invoice->serie) {
            InvoiceSerieType::PROFORMA => 'Proforma',
            InvoiceSerieType::INVOICE => 'Factura',
            InvoiceSerieType::SIMPLIFIED => 'Simplificada',
            InvoiceSerieType::RECTIFICATIVE => 'Rectificativa',
            default => 'Desconocido',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->invoice->status) {
            InvoiceStatus::DRAFT => 'Borrador',
            InvoiceStatus::SENT => 'Enviada',
            InvoiceStatus::PAID => 'Pagada',
            InvoiceStatus::OVERDUE => 'Vencida',
            InvoiceStatus::CANCELLED => 'Anulada',
            InvoiceStatus::PENDING => 'Pendiente',
            InvoiceStatus::CONVERTED => 'Convertida',
            default => 'Desconocido',
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->invoice->status) {
            InvoiceStatus::DRAFT => 'badge-ghost',
            InvoiceStatus::SENT => 'badge-info',
            InvoiceStatus::PAID => 'badge-success',
            InvoiceStatus::OVERDUE => 'badge-error',
            InvoiceStatus::CANCELLED => 'badge-neutral',
            InvoiceStatus::PENDING => 'badge-warning',
            InvoiceStatus::CONVERTED => 'badge-secondary',
            default => 'badge-ghost',
        };
    }

    public function canEdit(): bool
    {
        return $this->invoice->status === InvoiceStatus::DRAFT;
    }

    public function render(): View
    {
        return view('livewire.invoices.invoice-show');
    }
}
