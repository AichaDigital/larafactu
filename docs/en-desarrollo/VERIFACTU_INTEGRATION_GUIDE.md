# 📘 Guía de Uso - Integración Verifactu en Larabill

**Versión**: v0.5.0  
**Fecha**: 2025-11-24  
**Autor**: AI Development Team  
**Paquetes**: Larabill + Lara-Verifactu

---

## 🎯 **Resumen Ejecutivo**

Esta guía documenta cómo usar la integración completa entre **Larabill** (facturación base100) y **Lara-Verifactu** (AEAT/TicketBAI) para registrar facturas con la Agencia Tributaria Española.

### **Componentes Principales**

1. **VerifactuAdapter**: Convierte facturas de base100 (int) → decimal:2 (float)
2. **InvoiceVerifactuService**: Gestiona el registro con Verifactu
3. **AeatInvoiceValidator**: Valida requisitos AEAT pre-envío
4. **Invoice Model**: Métodos `requiresVAT()`, `isReverseCharge()`, `calculateTotals()`

---

## 📦 **Instalación y Setup**

### **Requisitos Previos**

```json
{
  "require": {
    "php": "^8.4",
    "aichadigital/larabill": "dev-main",
    "aichadigital/lara-verifactu": "dev-main",
    "aichadigital/lara100": "^1.1"
  }
}
```

### **Configuración**

```bash
# 1. Instalar paquetes (ya hecho en Larafactu)
composer update

# 2. Publicar configuración (si necesario)
php artisan vendor:publish --tag=larabill-config
php artisan vendor:publish --tag=verifactu-config

# 3. Ejecutar migraciones
php artisan migrate
```

---

## 🔧 **Uso Básico: Crear y Registrar Factura**

### **Escenario 1: Factura B2C España (21% IVA)**

```php
<?php

use AichaDigital\Larabill\Models\Invoice;
use AichaDigital\Larabill\Models\Customer;
use AichaDigital\Larabill\Models\Article;
use AichaDigital\Larabill\Services\InvoiceVerifactuService;
use AichaDigital\Larabill\Services\Validators\AeatInvoiceValidator;

// 1. Crear customer (cliente español)
$customer = Customer::factory()->create([
    'user_id'                => auth()->id(),
    'display_name'           => 'Juan Pérez',
    'legal_entity_type_code' => 'PERSONA_FISICA',
]);

$taxProfile = CustomerTaxProfile::factory()->create([
    'customer_id'   => $customer->id,
    'tax_code'      => '12345678Z', // DNI
    'country_code'  => 'ES',
    'vat_number'    => null, // Particulares no tienen VAT
]);

// 2. Crear artículo (servicio hosting)
$article = Article::factory()->create([
    'name'              => 'Hosting Básico Mensual',
    'unit_price'        => 10000, // €100.00 en base100
    'billing_frequency' => 'monthly',
]);

// 3. Crear factura
$invoice = Invoice::factory()->create([
    'user_id'          => auth()->id(),
    'customer_id'      => $customer->id,
    'tax_profile_id'   => $issuerTaxProfile->id, // Tu empresa
    'fiscal_number'    => 'FAC-2025-001',
    'series_number'    => 1,
    'taxable_amount'   => 0, // Calcular después
    'total_tax_amount' => 0,
    'total_amount'     => 0,
    'is_roi_taxed'     => false, // No es reverse charge
]);

// 4. Agregar items
$invoice->items()->create([
    'article_id'       => $article->id,
    'description'      => 'Hosting Básico - Noviembre 2025',
    'quantity'         => 100,   // 1 unidad en base100
    'unit_price'       => 10000, // €100.00
    'taxable_amount'   => 10000,
    'total_tax_amount' => 2100,  // 21% IVA = €21.00
    'total_amount'     => 12100, // €121.00
]);

// 5. Calcular totales automáticamente
$invoice->calculateTotals()->save();

// 6. Marcar como inmutable (fiscal final)
$invoice->update(['is_immutable' => true]);

// 7. Validar pre-envío AEAT
$validator = new AeatInvoiceValidator();
$validation = $validator->validate($invoice);

if (!$validation['valid']) {
    throw new \Exception('Validación fallida: ' . implode(', ', $validation['errors']));
}

// Mostrar warnings (opcional)
if (!empty($validation['warnings'])) {
    Log::warning('Factura con avisos', $validation['warnings']);
}

// 8. Registrar con Verifactu
$service = new InvoiceVerifactuService();

if ($service->isRegistered($invoice)) {
    throw new \Exception('Factura ya registrada con AEAT');
}

$verifactuInvoice = $service->registerInvoice($invoice, withBreakdowns: true);

// 9. Verificar resultado
dump([
    'larabill_id'      => $invoice->id,
    'verifactu_id'     => $verifactuInvoice->id,
    'fiscal_number'    => $invoice->fiscal_number,
    'total_base100'    => $invoice->total_amount,      // 12100 (int)
    'total_decimal'    => $verifactuInvoice->total_amount, // "121.00" (string)
    'aeat_registered'  => $service->isRegistered($invoice),
]);
```

---

## 🌍 **Escenario 2: Factura B2B Intracomunitaria (Reverse Charge)**

```php
// 1. Cliente francés (B2B con VAT válido)
$customerEU = Customer::factory()->create([
    'user_id'                => auth()->id(),
    'display_name'           => 'Entreprise SARL',
    'legal_entity_type_code' => 'J', // Sociedad
]);

$taxProfileEU = CustomerTaxProfile::factory()->create([
    'customer_id'         => $customerEU->id,
    'tax_code'            => 'FR12345678901',
    'country_code'        => 'FR',
    'vat_number'          => 'FR12345678901',
    'vat_number_verified' => true, // ¡Importante!
]);

// 2. Factura con reverse charge (sin IVA)
$invoice = Invoice::factory()->create([
    'user_id'          => auth()->id(),
    'customer_id'      => $customerEU->id,
    'tax_profile_id'   => $issuerTaxProfile->id,
    'fiscal_number'    => 'FAC-2025-002',
    'series_number'    => 2,
    'taxable_amount'   => 50000, // €500.00
    'total_tax_amount' => 0,     // Sin IVA (reverse charge)
    'total_amount'     => 50000, // €500.00
    'is_roi_taxed'     => true,  // ¡Importante! ROI activo
    'is_immutable'     => true,
]);

// 3. Verificar lógica ROI
dump([
    'requiresVAT'      => $invoice->requiresVAT(),      // false
    'isReverseCharge'  => $invoice->isReverseCharge(),  // true
]);

// 4. Validar y registrar (igual que B2C)
$validator = new AeatInvoiceValidator();
$validation = $validator->validate($invoice);

// Warning esperado: "ROI invoices typically have zero tax amount"
if (!empty($validation['warnings'])) {
    // Esto es correcto para ROI
}

$service = new InvoiceVerifactuService();
$verifactuInvoice = $service->registerInvoice($invoice);

// operation_key será "09" (Inversión del sujeto pasivo)
```

---

## 🔍 **Validaciones AEAT**

### **Uso del AeatInvoiceValidator**

```php
use AichaDigital\Larabill\Services\Validators\AeatInvoiceValidator;

$validator = new AeatInvoiceValidator();

// Opción 1: Validación completa
$result = $validator->validate($invoice);
/*
[
    'valid' => bool,
    'errors' => [
        'Invoice must be immutable before AEAT submission',
        'fiscal_number is required for AEAT',
        ...
    ],
    'warnings' => [
        'ROI invoices typically have zero tax amount (reverse charge)',
        'Invoice has no items (line items recommended for AEAT)',
        ...
    ]
]
*/

// Opción 2: Quick check
if ($validator->isValid($invoice)) {
    // OK para enviar
}

// Opción 3: Solo errores
$errors = $validator->getErrors($invoice);
if (!empty($errors)) {
    return response()->json(['errors' => $errors], 422);
}

// Opción 4: Solo warnings
$warnings = $validator->getWarnings($invoice);
```

### **Lista Completa de Validaciones**

#### **Errores (Bloquean envío)**:
- ❌ Invoice no inmutable (`is_immutable = false`)
- ❌ Falta `fiscal_number` o `series_number`
- ❌ Fecha `invoice_date` vacía o futura
- ❌ Sin `tax_profile` (issuer)
- ❌ Sin `customer`
- ❌ `total_amount <= 0`
- ❌ `taxable_amount` o `total_tax_amount` negativos
- ❌ `total_amount ≠ taxable_amount + total_tax_amount`
- ❌ Tax code vacío o formato inválido
- ❌ Factura ya registrada con Verifactu

#### **Warnings (Permitir con aviso)**:
- ⚠️ ROI invoice con `total_tax_amount > 0`
- ⚠️ Invoice sin items (recomendable tener líneas)
- ⚠️ Tax profile sin dirección
- ⚠️ Tax code no cumple formato NIF español estándar

---

## 🧮 **Cálculo Automático de Totales**

### **Método `calculateTotals()`**

```php
// Escenario: Múltiples items
$invoice = Invoice::factory()->create([
    'taxable_amount'   => 0, // Se calculará
    'total_tax_amount' => 0,
    'total_amount'     => 0,
]);

$invoice->items()->create([
    'taxable_amount'   => 10000, // €100.00
    'total_tax_amount' => 2100,  // €21.00
    'total_amount'     => 12100,
]);

$invoice->items()->create([
    'taxable_amount'   => 5000,  // €50.00
    'total_tax_amount' => 1050,  // €10.50
    'total_amount'     => 6050,
]);

// Calcular totales
$invoice->calculateTotals();

dump($invoice->taxable_amount);   // 15000 (€150.00)
dump($invoice->total_tax_amount); // 3150  (€31.50)
dump($invoice->total_amount);     // 18150 (€181.50)

// Guardar cambios
$invoice->save();

// O método chaining
$invoice->calculateTotals()->save();
```

### **Uso con Observer (Automático)**

```php
// app/Observers/InvoiceObserver.php
class InvoiceObserver
{
    public function saving(Invoice $invoice)
    {
        if ($invoice->items()->exists()) {
            $invoice->calculateTotals();
        }
    }
}

// AppServiceProvider
Invoice::observe(InvoiceObserver::class);
```

---

## 🔐 **Gestión de Estado: Inmutabilidad**

### **¿Cuándo marcar como inmutable?**

```php
// ❌ NUNCA inmutable antes de completar
$invoice = Invoice::create([...]);
$invoice->items()->create([...]);
// $invoice->is_immutable = true; // ¡NO AQUÍ!

// ✅ CORRECTO: Inmutable al finalizar
$invoice->calculateTotals()->save();
$invoice->update(['is_immutable' => true]);

// Ahora NO se puede editar
try {
    $invoice->update(['total_amount' => 99999]);
} catch (\Exception $e) {
    // "Cannot update an immutable invoice"
}
```

### **Excepción: Conversión a Proforma**

```php
// Solo permitido actualizar campos de conversión
$invoice->update([
    'converted_invoice_id' => $newInvoice->id,
    'converted_at'         => now(),
]);
// Esto SÍ funciona incluso si is_immutable = true
```

---

## 📊 **Verificar Registro con Verifactu**

```php
$service = new InvoiceVerifactuService();

// Verificar si ya está registrada
if ($service->isRegistered($invoice)) {
    $verifactuInvoice = $service->getVerifactuInvoice($invoice);
    
    dump([
        'status'        => 'already_registered',
        'verifactu_id'  => $verifactuInvoice->id,
        'registered_at' => $verifactuInvoice->created_at,
    ]);
    
    return;
}

// Primera vez: registrar
$verifactuInvoice = $service->registerInvoice($invoice);
```

---

## 🧪 **Testing**

### **Test Unitario: Validación**

```php
use AichaDigital\Larabill\Services\Validators\AeatInvoiceValidator;

it('validates correct invoice for AEAT', function () {
    $invoice = Invoice::factory()->create([
        'fiscal_number'    => 'FAC-2025-001',
        'series_number'    => 1,
        'taxable_amount'   => 10000,
        'total_tax_amount' => 2100,
        'total_amount'     => 12100,
        'is_immutable'     => true,
    ]);

    $validator = new AeatInvoiceValidator();
    
    expect($validator->isValid($invoice))->toBeTrue();
    expect($validator->getErrors($invoice))->toBeEmpty();
});
```

### **Test de Integración: Registro**

```php
use AichaDigital\Larabill\Services\InvoiceVerifactuService;

it('registers invoice with Verifactu successfully', function () {
    $invoice = Invoice::factory()->create([
        'taxable_amount'   => 10000,
        'total_tax_amount' => 2100,
        'total_amount'     => 12100,
        'is_immutable'     => true,
    ]);

    $service = new InvoiceVerifactuService();
    $verifactuInvoice = $service->registerInvoice($invoice);

    expect($verifactuInvoice->base_amount)->toBe('100.00');
    expect($verifactuInvoice->tax_amount)->toBe('21.00');
    expect($verifactuInvoice->total_amount)->toBe('121.00');
    expect($service->isRegistered($invoice))->toBeTrue();
});
```

---

## 🚨 **Manejo de Errores**

### **Try-Catch Pattern**

```php
use AichaDigital\Larabill\Services\InvoiceVerifactuService;
use Illuminate\Support\Facades\Log;

try {
    $service = new InvoiceVerifactuService();
    
    // Validar interno del servicio
    $validation = $service->validateForVerifactu($invoice);
    if (!$validation['valid']) {
        throw new \Exception('Validation failed: ' . implode(', ', $validation['errors']));
    }
    
    // Registrar
    $verifactuInvoice = $service->registerInvoice($invoice);
    
    Log::info('Invoice registered with Verifactu', [
        'invoice_id'    => $invoice->id,
        'verifactu_id'  => $verifactuInvoice->id,
    ]);
    
    return $verifactuInvoice;
    
} catch (\Exception $e) {
    Log::error('Verifactu registration failed', [
        'invoice_id' => $invoice->id,
        'error'      => $e->getMessage(),
        'trace'      => $e->getTraceAsString(),
    ]);
    
    throw $e;
}
```

---

## 📈 **Jobs en Background (Producción)**

### **Job: RegisterInvoiceWithVerifactu**

```php
<?php

namespace App\Jobs;

use AichaDigital\Larabill\Models\Invoice;
use AichaDigital\Larabill\Services\InvoiceVerifactuService;
use AichaDigital\Larabill\Services\Validators\AeatInvoiceValidator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RegisterInvoiceWithVerifactu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
    }

    public function handle(): void
    {
        $validator = new AeatInvoiceValidator();
        $service   = new InvoiceVerifactuService();

        // Validar
        if (!$validator->isValid($this->invoice)) {
            Log::error('Invoice validation failed', [
                'invoice_id' => $this->invoice->id,
                'errors'     => $validator->getErrors($this->invoice),
            ]);
            
            $this->fail('Validation failed');
            return;
        }

        // Verificar si ya está registrada
        if ($service->isRegistered($this->invoice)) {
            Log::info('Invoice already registered, skipping', [
                'invoice_id' => $this->invoice->id,
            ]);
            return;
        }

        // Registrar
        try {
            $verifactuInvoice = $service->registerInvoice($this->invoice);
            
            Log::info('Invoice registered successfully', [
                'invoice_id'    => $this->invoice->id,
                'verifactu_id'  => $verifactuInvoice->id,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Verifactu registration failed', [
                'invoice_id' => $this->invoice->id,
                'error'      => $e->getMessage(),
            ]);
            
            $this->fail($e);
        }
    }
}
```

### **Uso del Job**

```php
// Controller
public function finalizeInvoice(Invoice $invoice)
{
    // Marcar como inmutable
    $invoice->update(['is_immutable' => true]);
    
    // Dispatch job
    RegisterInvoiceWithVerifactu::dispatch($invoice);
    
    return response()->json([
        'message' => 'Invoice finalized, Verifactu registration queued',
        'invoice_id' => $invoice->id,
    ]);
}
```

---

## 🔄 **Flujo Completo: Controller Example**

```php
<?php

namespace App\Http\Controllers;

use AichaDigital\Larabill\Models\Invoice;
use AichaDigital\Larabill\Services\InvoiceVerifactuService;
use AichaDigital\Larabill\Services\Validators\AeatInvoiceValidator;
use App\Jobs\RegisterInvoiceWithVerifactu;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function finalize(Request $request, Invoice $invoice)
    {
        // 1. Recalcular totales
        $invoice->calculateTotals()->save();

        // 2. Marcar como inmutable
        $invoice->update(['is_immutable' => true]);

        // 3. Validar pre-envío
        $validator = new AeatInvoiceValidator();
        $validation = $validator->validate($invoice);

        if (!$validation['valid']) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validation['errors'],
            ], 422);
        }

        // 4. Dispatch job o enviar directamente
        if ($request->input('async', true)) {
            RegisterInvoiceWithVerifactu::dispatch($invoice);
            
            return response()->json([
                'message' => 'Invoice finalized, registration queued',
                'invoice_id' => $invoice->id,
            ]);
        }

        // 5. Envío síncrono
        try {
            $service = new InvoiceVerifactuService();
            $verifactuInvoice = $service->registerInvoice($invoice);

            return response()->json([
                'message' => 'Invoice finalized and registered',
                'invoice_id' => $invoice->id,
                'verifactu_id' => $verifactuInvoice->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Registration failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkVerifactuStatus(Invoice $invoice)
    {
        $service = new InvoiceVerifactuService();

        if (!$service->isRegistered($invoice)) {
            return response()->json([
                'status' => 'not_registered',
                'invoice_id' => $invoice->id,
            ]);
        }

        $verifactuInvoice = $service->getVerifactuInvoice($invoice);

        return response()->json([
            'status' => 'registered',
            'invoice_id' => $invoice->id,
            'verifactu_id' => $verifactuInvoice->id,
            'registered_at' => $verifactuInvoice->created_at,
        ]);
    }
}
```

---

## 📋 **Checklist Pre-Producción**

### **Antes de enviar a AEAT**:

- [ ] Invoice tiene `fiscal_number` y `series_number` válidos
- [ ] `invoice_date` no es futura
- [ ] `is_immutable = true`
- [ ] `calculateTotals()` ejecutado correctamente
- [ ] Tax profile del issuer completo (NIF, nombre, dirección)
- [ ] Customer y tax profile válidos
- [ ] `total = taxable + tax` (verificado)
- [ ] Para ROI: `is_roi_taxed = true` y `total_tax_amount = 0`
- [ ] Ejecutar `AeatInvoiceValidator::validate()` sin errores
- [ ] NO registrada previamente con Verifactu

---

## 🐛 **Troubleshooting**

### **Error: "Invoice must be immutable"**
```php
$invoice->update(['is_immutable' => true]);
```

### **Error: "total_amount must equal taxable_amount + total_tax_amount"**
```php
$invoice->calculateTotals()->save();
```

### **Error: "Invoice is already registered with Verifactu"**
```php
$service = new InvoiceVerifactuService();
if ($service->isRegistered($invoice)) {
    // Obtener la existente
    $existing = $service->getVerifactuInvoice($invoice);
}
```

### **Warning: "ROI invoices typically have zero tax amount"**
```php
// Para B2B EU, asegurar:
$invoice->is_roi_taxed = true;
$invoice->total_tax_amount = 0;
$invoice->total_amount = $invoice->taxable_amount;
```

---

## 📚 **Referencias**

- **Larabill Docs**: `/packages/aichadigital/larabill/README.md`
- **Lara-Verifactu Docs**: `/packages/aichadigital/lara-verifactu/README.md`
- **Tests**: `/packages/aichadigital/larabill/tests/Unit/Services/`
- **AEAT TicketBAI**: https://www.agenciatributaria.gob.es/AEAT.internet/Inicio/La_Agencia_Tributaria/Campanas/Verifactu/

---

## ✅ **Resumen de Clases**

| Clase | Propósito | Namespace |
|-------|-----------|-----------|
| `VerifactuAdapter` | Conversión base100 → decimal | `AichaDigital\Larabill\Services\Adapters` |
| `InvoiceVerifactuService` | Gestión registro Verifactu | `AichaDigital\Larabill\Services` |
| `AeatInvoiceValidator` | Validación requisitos AEAT | `AichaDigital\Larabill\Services\Validators` |
| `Invoice` | Modelo factura + métodos | `AichaDigital\Larabill\Models` |

---

**Última actualización**: 2025-11-24  
**Mantenido por**: Aicha Digital Development Team

