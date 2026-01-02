<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use AichaDigital\Larabill\Models\CompanyFiscalConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin Fiscal Config Edit component.
 *
 * @see ADR-004 Authorization System
 */
#[Layout('components.layouts.app')]
#[Title('Editar Configuracion Fiscal - Admin')]
class FiscalConfigEdit extends Component
{
    public CompanyFiscalConfig $fiscalConfig;

    // Business identity
    public string $business_name = '';

    public string $tax_id = '';

    public string $legal_entity_type = '';

    // Address
    public string $address = '';

    public string $city = '';

    public string $state = '';

    public string $zip_code = '';

    public string $country_code = 'ES';

    // Fiscal settings
    public bool $is_oss = false;

    public bool $is_roi = false;

    public string $currency = 'EUR';

    public string $fiscal_year_start = '01-01';

    // Notes
    public string $notes = '';

    /**
     * Valid country codes (EU + common).
     */
    protected array $validCountryCodes = [
        'ES', 'PT', 'FR', 'DE', 'IT', 'GB', 'IE', 'NL', 'BE', 'LU',
        'AT', 'CH', 'PL', 'CZ', 'SK', 'HU', 'RO', 'BG', 'GR', 'HR',
        'SI', 'DK', 'SE', 'FI', 'NO', 'EE', 'LV', 'LT', 'MT', 'CY',
    ];

    /**
     * Valid currency codes.
     */
    protected array $validCurrencies = [
        'EUR', 'USD', 'GBP', 'CHF', 'SEK', 'NOK', 'DKK', 'PLN', 'CZK', 'HUF',
    ];

    /**
     * Mount the component.
     */
    public function mount(CompanyFiscalConfig $fiscalConfig): void
    {
        Gate::authorize('manage-users');

        $this->fiscalConfig = $fiscalConfig;
        $this->business_name = $fiscalConfig->business_name;
        $this->tax_id = $fiscalConfig->tax_id;
        $this->legal_entity_type = $fiscalConfig->legal_entity_type;
        $this->address = $fiscalConfig->address;
        $this->city = $fiscalConfig->city;
        $this->state = $fiscalConfig->state ?? '';
        $this->zip_code = $fiscalConfig->zip_code;
        $this->country_code = $fiscalConfig->country_code;
        $this->is_oss = $fiscalConfig->is_oss;
        $this->is_roi = $fiscalConfig->is_roi;
        $this->currency = $fiscalConfig->currency;
        $this->fiscal_year_start = $fiscalConfig->fiscal_year_start ?? '01-01';
        $this->notes = $fiscalConfig->notes ?? '';
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['required', 'string', 'max:20'],
            'legal_entity_type' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['required', 'string', 'max:10'],
            'country_code' => ['required', 'string', 'size:2', Rule::in($this->validCountryCodes)],
            'is_oss' => ['boolean'],
            'is_roi' => ['boolean'],
            'currency' => ['required', 'string', 'size:3', Rule::in($this->validCurrencies)],
            'fiscal_year_start' => ['nullable', 'string', 'max:5'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'business_name.required' => 'La razon social es obligatoria.',
            'tax_id.required' => 'El NIF/CIF es obligatorio.',
            'legal_entity_type.required' => 'El tipo de entidad es obligatorio.',
            'address.required' => 'La direccion es obligatoria.',
            'city.required' => 'La ciudad es obligatoria.',
            'zip_code.required' => 'El codigo postal es obligatorio.',
            'country_code.required' => 'El codigo de pais es obligatorio.',
            'country_code.in' => 'El codigo de pais no es valido.',
            'currency.in' => 'La moneda no es valida.',
        ];
    }

    /**
     * Update the fiscal configuration.
     */
    public function update(): void
    {
        Gate::authorize('manage-users');

        $validated = $this->validate();

        $this->fiscalConfig->update([
            'business_name' => $validated['business_name'],
            'tax_id' => $validated['tax_id'],
            'legal_entity_type' => $validated['legal_entity_type'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'] ?: null,
            'zip_code' => $validated['zip_code'],
            'country_code' => $validated['country_code'],
            'is_oss' => $validated['is_oss'],
            'is_roi' => $validated['is_roi'],
            'currency' => $validated['currency'],
            'fiscal_year_start' => $validated['fiscal_year_start'] ?: '01-01',
            'notes' => $validated['notes'] ?: null,
        ]);

        session()->flash('success', 'Configuracion fiscal actualizada correctamente.');

        $this->redirect(route('admin.fiscal-configs'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.fiscal-config-edit', [
            'countryCodes' => $this->validCountryCodes,
            'currencies' => $this->validCurrencies,
        ]);
    }
}
