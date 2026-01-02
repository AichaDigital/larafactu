<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

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

    public ?object $taxProfile = null;

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

        // Load active tax profile using DB::table
        $this->taxProfile = DB::table('user_tax_profiles')
            ->where('user_id', $customer->id)
            ->where('is_active', true)
            ->whereNull('valid_until')
            ->first();

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

            if ($fiscalDataChanged && $this->taxProfile) {
                // Close current profile using DB::table
                DB::table('user_tax_profiles')
                    ->where('id', $this->taxProfile->id)
                    ->update([
                        'valid_until' => now()->subDay(),
                        'is_active' => false,
                        'updated_at' => now(),
                    ]);

                // Create new profile
                DB::table('user_tax_profiles')->insert([
                    'user_id' => $this->customer->id,
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
                    'valid_until' => null,
                    'is_active' => true,
                    'notes' => $this->notes ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif (! $this->taxProfile) {
                // No existing profile, create first one
                DB::table('user_tax_profiles')->insert([
                    'user_id' => $this->customer->id,
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
                    'valid_until' => null,
                    'is_active' => true,
                    'notes' => $this->notes ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
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
            || $this->isCompany !== ($this->taxProfile->is_company ?? false)
            || $this->isEuVatRegistered !== ($this->taxProfile->is_eu_vat_registered ?? false)
            || $this->isExemptVat !== ($this->taxProfile->is_exempt_vat ?? false);
    }

    public function render(): View
    {
        return view('livewire.customers.customer-edit');
    }
}
