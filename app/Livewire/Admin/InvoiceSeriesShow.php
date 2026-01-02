<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use AichaDigital\Larabill\Models\InvoiceSeriesControl;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin Invoice Series Show component.
 *
 * @see ADR-004 Authorization System
 */
#[Layout('components.layouts.app')]
#[Title('Ver Serie de Facturacion - Admin')]
class InvoiceSeriesShow extends Component
{
    public InvoiceSeriesControl $invoiceSeries;

    /**
     * Mount the component.
     */
    public function mount(InvoiceSeriesControl $invoiceSeries): void
    {
        Gate::authorize('manage-users');

        $this->invoiceSeries = $invoiceSeries;
    }

    /**
     * Get the serie type label.
     */
    public function getSerieLabel(): string
    {
        return match ($this->invoiceSeries->serie->value) {
            0 => 'Proforma',
            1 => 'Factura',
            2 => 'Simplificada (Ticket)',
            3 => 'Rectificativa',
            default => 'Desconocido',
        };
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.invoice-series-show', [
            'serieLabel' => $this->getSerieLabel(),
        ]);
    }
}
