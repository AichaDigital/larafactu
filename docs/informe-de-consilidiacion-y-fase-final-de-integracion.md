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

### Pendiente critico

| Item | Estado | Bloqueo |
|------|--------|---------|
| Wizard AdminStep solo UUID v7 | PENDIENTE | Necesita opcion Integer |
| Tests integracion inter-paquetes | LIMITADOS | Solo SpanishB2CInvoiceTest |
| Soporte Integer en wizard | NO IMPLEMENTADO | Requiere refactor AdminStep |

### Detalle tecnico: Soporte de IDs

**larabill config/larabill.php**:

```php
'user_id_type' => env('LARABILL_USER_ID_TYPE', 'uuid'), // int, uuid, ulid, auto
```

**installer/src/Steps/AdminStep.php (linea 79)**:

```php
$userId = $this->generateUuidV7(); // HARDCODED - no soporta Integer
```

**Solucion propuesta**:

1. Añadir paso de seleccion de tipo ID en wizard
2. Modificar AdminStep para usar `MigrationHelper::getUserIdType()`
3. Ajustar migracion de users segun tipo seleccionado

## Conclusion

Larafactu debe seguir siendo un instalador opinado con dos variantes de ID, mientras los paquetes se mantienen estrictamente agnosticos. La fase final de integracion debe asegurar compatibilidad total entre ambos mundos, sin comprometer la reutilizacion externa de los paquetes.

---

Ultima actualizacion: 2026-01-22
