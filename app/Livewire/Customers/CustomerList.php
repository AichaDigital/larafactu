<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use AichaDigital\Larabill\Models\UserTaxProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Clientes')]
class CustomerList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filter = 'all';

    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $user = User::find($id);

        if (! $user) {
            session()->flash('error', 'Cliente no encontrado.');

            return;
        }

        // Check if user has invoices
        $hasInvoices = $user->ownedTaxProfiles()
            ->whereHas('invoices')
            ->exists();

        if ($hasInvoices) {
            session()->flash('error', 'No se puede eliminar un cliente con facturas asociadas.');

            return;
        }

        // Delete tax profiles first
        $user->ownedTaxProfiles()->delete();

        $user->delete();

        session()->flash('success', 'Cliente eliminado correctamente.');
    }

    public function render(): View
    {
        // Build base query with tax profile relationship
        $query = User::query()
            ->whereHas('ownedTaxProfiles');

        // Apply search filter
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('ownedTaxProfiles', function ($tp) use ($search) {
                        $tp->where('tax_id', 'like', "%{$search}%")
                            ->orWhere('fiscal_name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply type filter
        if ($this->filter === 'company') {
            $query->whereHas('ownedTaxProfiles', function ($tp) {
                $tp->active()->where('is_company', true);
            });
        } elseif ($this->filter === 'individual') {
            $query->whereHas('ownedTaxProfiles', function ($tp) {
                $tp->active()->where('is_company', false);
            });
        }

        $customers = $query->orderBy('name')->paginate($this->perPage);

        // Eager load active tax profiles using model
        $customerIds = $customers->pluck('id')->toArray();
        $taxProfiles = collect();

        if (! empty($customerIds)) {
            $taxProfiles = UserTaxProfile::query()
                ->whereIn('owner_user_id', $customerIds)
                ->active()
                ->get()
                ->keyBy('owner_user_id');
        }

        return view('livewire.customers.customer-list', [
            'customers' => $customers,
            'taxProfiles' => $taxProfiles,
        ]);
    }
}
