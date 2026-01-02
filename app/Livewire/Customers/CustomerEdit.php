<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use AichaDigital\Larabill\Models\UserTaxProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Editar Cliente')]
class CustomerEdit extends Component
{
    public User $customer;

    public ?UserTaxProfile $taxProfile = null;

    // User fields
    public string $name = '';

    public string $email = '';

    // Tax profile fields
    public string $fiscalName = '';

    public string $taxId = '';

    public string $address = '';

    public string $city = '';

    public string $state = '';

    public string $zipCode = '';

    public string $countryCode = 'ES';

    public bool $isCompany = false;

    public bool $isEuVatRegistered = false;

    public bool $isExemptVat = false;

    public string $notes = '';

    public function mount(User $customer): void
    {
        $this->customer = $customer;

        // Load user data
        $this->name = $customer->name;
        $this->email = $customer->email;

        // Load active tax profile using model
        $this->taxProfile = UserTaxProfile::getActiveForOwner($customer->id);

        if ($this->taxProfile) {
            $this->fiscalName = $this->taxProfile->fiscal_name ?? '';
            $this->taxId = $this->taxProfile->tax_id ?? '';
            $this->address = $this->taxProfile->address ?? '';
            $this->city = $this->taxProfile->city ?? '';
            $this->state = $this->taxProfile->state ?? '';
            $this->zipCode = $this->taxProfile->zip_code ?? '';
            $this->countryCode = $this->taxProfile->country_code ?? 'ES';
            $this->isCompany = $this->taxProfile->is_company ?? false;
            $this->isEuVatRegistered = $this->taxProfile->is_eu_vat_registered ?? false;
            $this->isExemptVat = $this->taxProfile->is_exempt_vat ?? false;
            $this->notes = $this->taxProfile->notes ?? '';
        } else {
            $this->fiscalName = $customer->name;
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->customer->id),
            ],
            'fiscalName' => 'required|string|max:255',
            'taxId' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zipCode' => 'nullable|string|max:20',
            'countryCode' => 'required|string|size:2',
            'isCompany' => 'boolean',
            'isEuVatRegistered' => 'boolean',
            'isExemptVat' => 'boolean',
            'notes' => 'nullable|string',
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            // Update user
            $this->customer->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            // Check if fiscal data changed
            $fiscalDataChanged = $this->hasFiscalDataChanged();

            if ($fiscalDataChanged || ! $this->taxProfile) {
                // Create new profile (model auto-closes previous one)
                UserTaxProfile::createForOwner($this->customer->id, [
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
            }
        });

        session()->flash('success', 'Cliente actualizado correctamente.');

        $this->redirect(route('customers.index'), navigate: true);
    }

    protected function hasFiscalDataChanged(): bool
    {
        if (! $this->taxProfile) {
            return true;
        }

        return $this->fiscalName !== ($this->taxProfile->fiscal_name ?? '')
            || $this->taxId !== ($this->taxProfile->tax_id ?? '')
            || $this->address !== ($this->taxProfile->address ?? '')
            || $this->city !== ($this->taxProfile->city ?? '')
            || $this->state !== ($this->taxProfile->state ?? '')
            || $this->zipCode !== ($this->taxProfile->zip_code ?? '')
            || $this->countryCode !== ($this->taxProfile->country_code ?? 'ES')
            || $this->isCompany !== (bool) ($this->taxProfile->is_company ?? false)
            || $this->isEuVatRegistered !== (bool) ($this->taxProfile->is_eu_vat_registered ?? false)
            || $this->isExemptVat !== (bool) ($this->taxProfile->is_exempt_vat ?? false);
    }

    public function render(): View
    {
        return view('livewire.customers.customer-edit');
    }
}
