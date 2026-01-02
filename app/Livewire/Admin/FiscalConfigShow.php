<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use AichaDigital\Larabill\Models\CompanyFiscalConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin Fiscal Config Show component.
 *
 * @see ADR-004 Authorization System
 */
#[Layout('components.layouts.app')]
#[Title('Ver Configuracion Fiscal - Admin')]
class FiscalConfigShow extends Component
{
    public CompanyFiscalConfig $fiscalConfig;

    /**
     * Mount the component.
     */
    public function mount(CompanyFiscalConfig $fiscalConfig): void
    {
        Gate::authorize('manage-users');

        $this->fiscalConfig = $fiscalConfig;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.fiscal-config-show');
    }
}
