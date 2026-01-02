<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
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

        // Check if user has invoices using DB query
        $hasInvoices = DB::table('invoices')
            ->where('customer_user_id', $id)
            ->exists();

        if ($hasInvoices) {
            session()->flash('error', 'No se puede eliminar un cliente con facturas asociadas.');

            return;
        }

        // Delete tax profiles first using DB query
        DB::table('user_tax_profiles')
            ->where('user_id', $id)
            ->delete();

        $user->delete();

        session()->flash('success', 'Cliente eliminado correctamente.');
    }

    public function render(): View
    {
        // Build base query
        $query = User::query();

        // Apply search filter
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereExists(function ($sub) use ($search) {
                        $sub->select(DB::raw(1))
                            ->from('user_tax_profiles')
                            ->whereColumn('user_tax_profiles.user_id', 'users.id')
                            ->where(function ($tp) use ($search) {
                                $tp->where('tax_id', 'like', "%{$search}%")
                                    ->orWhere('fiscal_name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        // Apply type filter
        if ($this->filter === 'company') {
            $query->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('user_tax_profiles')
                    ->whereColumn('user_tax_profiles.user_id', 'users.id')
                    ->where('is_company', true)
                    ->where('is_active', true);
            });
        } elseif ($this->filter === 'individual') {
            $query->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('user_tax_profiles')
                    ->whereColumn('user_tax_profiles.user_id', 'users.id')
                    ->where('is_company', false)
                    ->where('is_active', true);
            });
        }

        $customers = $query->orderBy('name')->paginate($this->perPage);

        // Eager load tax profiles manually
        $customerIds = $customers->pluck('id')->toArray();
        $taxProfiles = collect();

        if (! empty($customerIds)) {
            $taxProfiles = DB::table('user_tax_profiles')
                ->whereIn('user_id', $customerIds)
                ->where('is_active', true)
                ->whereNull('valid_until')
                ->get()
                ->keyBy('user_id');
        }

        return view('livewire.customers.customer-list', [
            'customers' => $customers,
            'taxProfiles' => $taxProfiles,
        ]);
    }
}
