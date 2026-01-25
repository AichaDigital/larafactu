---
expires: 2026-02-08
review_cycle: quincenal
last_updated: 2026-01-25
owner: abkrim
---

# Estado del Trabajo - Larafactu

## Resumen

Refactor en curso: migración de Filament a DaisyUI+Livewire. ADR-005 define la estrategia.

## En Progreso

| Tarea | Propietario | Inicio | Bloqueadores |
|-------|-------------|--------|--------------|
| Estructura Blade+Livewire+DaisyUI | - | 2026-01 | Ninguno |
| Sistema de preferencias (user_preferences) | - | - | Estructura UI |

## Completado (Refactor 2026-01)

- ADR-005 creado (deprecación Filament)
- Temas DaisyUI documentados
- Paquetes *-filament marcados como ABANDONADOS
- ADR-004 limpieza larabill: eliminados Department, UserType, AccessLevel
- Renombrado user_id a owner_user_id en user_tax_profiles
- Añadido current_tax_profile_id a users (patrón perfiles compartidos)
- Creado SCHEMA_REQUIREMENTS.md en larabill

## Próximas Prioridades

- [ ] Remover Filament de composer.json larafactu
- [ ] Crear estructura base Blade+Livewire+DaisyUI
- [ ] Implementar sistema de preferencias (user_preferences)
- [ ] Reconstruir UI principal

## Bloqueadores Activos

Ninguno actualmente.

## Decisiones Pendientes

- **ADR-004 (Autorización)**: Revisar si authorization debe ir en app o en paquetes. Estado actual: EN REVISION en CLAUDE.md.

## Referencias

- ADR-005: `docs/architecture/ADR_005_FILAMENT_DEPRECATION_DAISYUI.md`
- Temas DaisyUI: `docs/themes/daisyui-themes.md`
- Template Nexus: `docs/themes/nexus/`
