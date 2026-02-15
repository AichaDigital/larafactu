<?php

declare(strict_types=1);

use App\Livewire\Invoices\InvoiceCreate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('InvoiceCreate numbering migration', function () {
    it('no longer has artisanal getNextSeriesNumber method', function () {
        $reflection = new ReflectionClass(InvoiceCreate::class);

        expect($reflection->hasMethod('getNextSeriesNumber'))->toBeFalse(
            'InvoiceCreate should not have getNextSeriesNumber() — use InvoiceNumberingService'
        );
    });

    it('no longer has artisanal generateFiscalNumber method', function () {
        $reflection = new ReflectionClass(InvoiceCreate::class);

        expect($reflection->hasMethod('generateFiscalNumber'))->toBeFalse(
            'InvoiceCreate should not have generateFiscalNumber() — use InvoiceNumberingService'
        );
    });

    it('uses InvoiceNumberingService import', function () {
        $source = file_get_contents(app_path('Livewire/Invoices/InvoiceCreate.php'));

        expect($source)->toContain('use AichaDigital\Larabill\Services\InvoiceNumberingService;');
        expect($source)->toContain('InvoiceNumberingService::class');
    });
});
