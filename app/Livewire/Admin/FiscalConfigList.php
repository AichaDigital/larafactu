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
 * Admin Fiscal Config List component.
 *
 * @see ADR-004 Authorization System
 */
#[Layout('components.layouts.app')]
#[Title('Configuracion Fiscal - Admin')]
class FiscalConfigList extends Component
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
        $configs = CompanyFiscalConfig::orderByDesc('valid_from')->get();

        return view('livewire.admin.fiscal-config-list', [
            'configs' => $configs,
        ]);
    }
}
