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
 * Admin Invoice Series Edit component.
 *
 * @see ADR-004 Authorization System
 */
#[Layout('components.layouts.app')]
#[Title('Editar Serie de Facturacion - Admin')]
class InvoiceSeriesEdit extends Component
{
    public InvoiceSeriesControl $invoiceSeries;

    public string $prefix = '';

    public string $number_format = '';

    public bool $reset_annually = true;

    public bool $is_active = true;

    public string $description = '';

    /**
     * Mount the component.
     */
    public function mount(InvoiceSeriesControl $invoiceSeries): void
    {
        Gate::authorize('manage-users');

        $this->invoiceSeries = $invoiceSeries;
        $this->prefix = $invoiceSeries->prefix;
        $this->number_format = $invoiceSeries->number_format;
        $this->reset_annually = $invoiceSeries->reset_annually;
        $this->is_active = $invoiceSeries->is_active;
        $this->description = $invoiceSeries->description ?? '';
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
     * Get the serie type label.
     */
    public function getSerieLabel(): string
    {
        return $this->serieTypes[$this->invoiceSeries->serie->value] ?? 'Desconocido';
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
                    ->where('serie', $this->invoiceSeries->serie->value)
                    ->where('fiscal_year', $this->invoiceSeries->fiscal_year)
                    ->whereNull('user_id')
                    ->ignore($this->invoiceSeries->id),
            ],
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
            'number_format.required' => 'El formato de numero es obligatorio.',
        ];
    }

    /**
     * Update the invoice series.
     */
    public function update(): void
    {
        Gate::authorize('manage-users');

        $validated = $this->validate();

        $this->invoiceSeries->update([
            'prefix' => $validated['prefix'],
            'number_format' => $validated['number_format'],
            'reset_annually' => $validated['reset_annually'],
            'is_active' => $validated['is_active'],
            'description' => $validated['description'] ?: null,
        ]);

        session()->flash('success', "Serie {$validated['prefix']} actualizada correctamente.");

        $this->redirect(route('admin.invoice-series'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.invoice-series-edit', [
            'serieLabel' => $this->getSerieLabel(),
        ]);
    }
}
