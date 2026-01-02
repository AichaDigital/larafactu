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
 * Admin Invoice Series List component.
 *
 * @see ADR-004 Authorization System
 */
#[Layout('components.layouts.app')]
#[Title('Series de Facturacion - Admin')]
class InvoiceSeriesList extends Component
{
    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('manage-users');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $series = InvoiceSeriesControl::orderByDesc('fiscal_year')
            ->orderBy('prefix')
            ->get();

        return view('livewire.admin.invoice-series-list', [
            'series' => $series,
        ]);
    }
}
