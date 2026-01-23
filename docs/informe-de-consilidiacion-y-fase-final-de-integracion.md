# Informe de consolidacion y fase final de integracion

## Proposito
Larafactu es una aplicacion de staging que integra paquetes AichaDigital para facturacion, ROI, Verifactu y tickets. Su objetivo es servir como instalador y orquestador de la experiencia completa, mientras mantiene los paquetes base agnosticos para uso de terceros.

## Principios no negociables
- Larafactu actua como instalador opinado con flujo guiado (CLI y wizard).
- Los paquetes deben permanecer agnosticos, contract-first y reutilizables.
- Filament no se usa ni debe usarse en Larafactu ni en el ecosistema.

## Arquitectura funcional
Larafactu integra los paquetes en una capa de aplicacion que define:
- UI y flujos de usuario
- Policies y gates de autorizacion
- Instalacion guiada, seeds y orden de migraciones
- Preferencias de usuario y temas

Los paquetes aportan:
- Logica de negocio
- Contratos y modelos base
- Migrations publicables
- Servicios desacoplados

## Instalador Larafactu con dos variantes de ID
Larafactu debe soportar dos variantes de instalacion.

### Variante A: UUID v7 string
- IDs tipo UUID string en users y modelos relacionados.
- Compatible con el enfoque recomendado en Larabill.

### Variante B: Integer autoincrement
- IDs enteros para instalaciones legacy o necesidades especificas.

En ambos casos, el instalador debe:
- Configurar el tipo de ID de usuario
- Ejecutar migraciones en el orden correcto
- Validar requisitos de esquema antes de finalizar

## Agnosticismo de paquetes
Los paquetes deben mantenerse independientes de:
- UI
- Autorizacion de la aplicacion
- Flujos especificos del instalador

Cada paquete debe exponer:
- Contratos publicos
- Configuracion desacoplada
- Migrations publicables
- Servicios integrables por terceros

## Choques detectados y resolucion

### Choque 1: Instalador opinado vs paquetes agnosticos
El instalador impone flujo y estructura, mientras los paquetes declaran agnosticismo.

Resolucion:
- Documentar explicitamente la separacion de capas.
- El instalador debe adaptarse a los contratos del paquete, no al reves.
- Los requisitos de esquema deben estar centralizados y validados.

### Choque 2: Modos nativo vs custom en paquetes
Algunos paquetes permiten modo nativo y modo adaptado por contratos.

Resolucion:
- Larafactu opera en modo nativo.
- La documentacion debe aclarar que terceros pueden usar modo custom sin depender del instalador.

### Choque 3: Residuos de Filament en documentacion
Existen menciones historicas a Filament en algunos documentos.

Resolucion:
- Marcar cualquier referencia como obsoleta o moverla fuera del flujo principal.

## Fase final de integracion
La fase final debe enfocarse en integracion real entre paquetes y Larafactu, con cobertura de pruebas end-to-end y verificacion de coherencia de esquema.

### Objetivos
- Integracion estable entre Larabill, Lararoi, Lara Verifactu y Laratickets.
- Verificacion completa del instalador en ambos modos de ID.
- Validacion funcional del wizard y comandos de instalacion.

### Entregables
- Checklist de esquema compatible por paquete.
- Pruebas de integracion para:
  - Facturacion con perfiles fiscales y cambios temporales
  - Verificacion ROI en facturas B2B
  - Registro Verifactu y cadena de hashes
  - Tickets con departamentos y permisos delegados
- Validacion de compatibilidad UUID/Integer en todo el flujo.

### Criterios de salida
- Instalador ejecuta correctamente en ambas variantes (UUID/Integer).
- Paquetes mantienen API publica y contratos sin acoplamiento a Larafactu.
- Pruebas de integracion pasan en entorno de staging.

## Estado actual (2026-01-22)

### Completado

- Paquetes core integrados: larabill, lara-verifactu, lararoi, laratickets
- Filament completamente eliminado de larabill (refactor bc47019)
- Symlinks Filament eliminados de larafactu
- SCHEMA_REQUIREMENTS.md creado en larabill (version 2.1)
- UI DaisyUI parcialmente migrada (~60%)
- MigrationHelper soporta int/uuid/ulid en larabill
- Wizard soporta seleccion UUID/Integer en DatabaseStep
- AdminStep genera ID segun tipo seleccionado (2026-01-23)
- LegalEntityTypesSeeder incluye campo is_company (2026-01-23)
- Tests EU B2B con ROI/reverse charge (EuB2BInvoiceTest)

### Pendiente

| Item | Estado | Nota |
|------|--------|------|
| Tests integracion inter-paquetes | PARCIAL | SpanishB2CInvoiceTest + EuB2BInvoiceTest |
| Validacion wizard con Integer | PENDIENTE | Requiere test manual en staging |

### Detalle tecnico: Soporte de IDs (IMPLEMENTADO)

**Flujo completo**:

1. DatabaseStep presenta selector UUID/Integer en UI
2. Usuario selecciona tipo de ID
3. DatabaseStep escribe `LARABILL_USER_ID_TYPE` en .env
4. DatabaseStep guarda `id_type` en state
5. AdminStep lee `id_type` del state
6. AdminStep genera UUID o usa auto-increment segun tipo

**larabill config/larabill.php**:

```php
'user_id_type' => env('LARABILL_USER_ID_TYPE', 'uuid'), // int, uuid, ulid, auto
```

**installer/src/Steps/AdminStep.php**:

```php
$dbConfig = $this->state->get('database');
$idType = $dbConfig['id_type'] ?? 'uuid';
$userId = $this->generateUserId($idType); // UUID o null para auto-increment
```

## Conclusion

Larafactu debe seguir siendo un instalador opinado con dos variantes de ID, mientras los paquetes se mantienen estrictamente agnosticos. La fase final de integracion debe asegurar compatibilidad total entre ambos mundos, sin comprometer la reutilizacion externa de los paquetes.

---

Ultima actualizacion: 2026-01-23
