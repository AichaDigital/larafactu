<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use AichaDigital\Larabill\Enums\InvoiceSerieType;
use AichaDigital\Larabill\Models\InvoiceSeriesControl;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin Invoice Series Create component.
 *
 * @see ADR-004 Authorization System
 */
#[Layout('components.layouts.app')]
#[Title('Nueva Serie de Facturacion - Admin')]
class InvoiceSeriesCreate extends Component
{
    public string $prefix = '';

    public string $serie = '';

    public string $fiscal_year = '';

    public ?int $start_number = null;

    public string $number_format = '{{prefix}}{{year}}-{{number}}';

    public bool $reset_annually = true;

    public bool $is_active = true;

    public string $description = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('manage-users');
        $this->fiscal_year = (string) now()->year;
        $this->start_number = 1;
    }

    /**
     * Get available serie types.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function serieTypes(): array
    {
        return [
            InvoiceSerieType::PROFORMA->value => 'Proforma',
            InvoiceSerieType::INVOICE->value => 'Factura',
            InvoiceSerieType::SIMPLIFIED->value => 'Simplificada (Ticket)',
            InvoiceSerieType::RECTIFICATIVE->value => 'Rectificativa',
        ];
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'prefix' => [
                'required',
                'string',
                'max:10',
                Rule::unique('invoice_series_control')
                    ->where('serie', $this->serie)
                    ->where('fiscal_year', $this->fiscal_year)
                    ->whereNull('user_id'),
            ],
            'serie' => ['required', 'integer', Rule::in([0, 1, 2, 3])],
            'fiscal_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'start_number' => ['required', 'integer', 'min:1'],
            'number_format' => ['required', 'string', 'max:100'],
            'reset_annually' => ['boolean'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string', 'max:255'],
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
            'prefix.required' => 'El prefijo es obligatorio.',
            'prefix.unique' => 'Ya existe una serie con este prefijo para el ano fiscal seleccionado.',
            'serie.required' => 'El tipo de serie es obligatorio.',
            'fiscal_year.required' => 'El ano fiscal es obligatorio.',
            'fiscal_year.integer' => 'El ano fiscal debe ser un numero.',
            'start_number.required' => 'El numero inicial es obligatorio.',
            'start_number.min' => 'El numero inicial debe ser al menos 1.',
            'number_format.required' => 'El formato de numero es obligatorio.',
        ];
    }

    /**
     * Create the invoice series.
     */
    public function create(): void
    {
        Gate::authorize('manage-users');

        $validated = $this->validate();

        $fiscalYear = (int) $validated['fiscal_year'];

        InvoiceSeriesControl::create([
            'prefix' => $validated['prefix'],
            'serie' => $validated['serie'],
            'fiscal_year' => $fiscalYear,
            'fiscal_year_start' => "{$fiscalYear}-01-01",
            'fiscal_year_end' => "{$fiscalYear}-12-31",
            'start_number' => $validated['start_number'],
            'last_number' => $validated['start_number'] - 1,
            'number_format' => $validated['number_format'],
            'reset_annually' => $validated['reset_annually'],
            'is_active' => $validated['is_active'],
            'description' => $validated['description'] ?: null,
            'user_id' => null, // Global series
        ]);

        session()->flash('success', "Serie {$validated['prefix']} creada correctamente.");

        $this->redirect(route('admin.invoice-series'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.invoice-series-create');
    }
}
