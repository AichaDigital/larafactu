<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use AichaDigital\Larabill\Models\UserTaxProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Nuevo Cliente')]
class CustomerCreate extends Component
{
    // User fields
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    // Tax profile fields
    #[Validate('required|string|max:255')]
    public string $fiscalName = '';

    #[Validate('nullable|string|max:20')]
    public string $taxId = '';

    #[Validate('nullable|string|max:255')]
    public string $address = '';

    #[Validate('nullable|string|max:100')]
    public string $city = '';

    #[Validate('nullable|string|max:100')]
    public string $state = '';

    #[Validate('nullable|string|max:20')]
    public string $zipCode = '';

    #[Validate('required|string|size:2')]
    public string $countryCode = 'ES';

    #[Validate('boolean')]
    public bool $isCompany = false;

    #[Validate('boolean')]
    public bool $isEuVatRegistered = false;

    #[Validate('boolean')]
    public bool $isExemptVat = false;

    #[Validate('nullable|string')]
    public string $notes = '';

    public function mount(): void
    {
        // Default fiscal name same as name
        $this->fiscalName = $this->name;
    }

    public function updatedName(): void
    {
        // Auto-fill fiscal name if empty
        if (empty($this->fiscalName)) {
            $this->fiscalName = $this->name;
        }
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            // Create user with random password (customer won't login initially)
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make(Str::random(32)),
            ]);

            // Create tax profile using model
            UserTaxProfile::createForOwner($user->id, [
                'fiscal_name' => $this->fiscalName,
                'tax_id' => $this->taxId ?: null,
                'address' => $this->address ?: null,
                'city' => $this->city ?: null,
                'state' => $this->state ?: null,
                'zip_code' => $this->zipCode ?: null,
                'country_code' => $this->countryCode,
                'is_company' => $this->isCompany,
                'is_eu_vat_registered' => $this->isEuVatRegistered,
                'is_exempt_vat' => $this->isExemptVat,
                'valid_from' => now(),
                'notes' => $this->notes ?: null,
            ]);
        });

        session()->flash('success', 'Cliente creado correctamente.');

        $this->redirect(route('customers.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.customers.customer-create');
    }
}
