# Temas DaisyUI para Larafactu

## Configuracion Principal

```css
@import "tailwindcss";
@plugin "daisyui" {
  themes: cupcake --default, abyss --prefersdark, corporate, sunset;
}
```

## Temas Seleccionados

### Tema Light por Defecto: cupcake

Tema claro, amigable y profesional.

### Tema Dark por Defecto: abyss

Tema oscuro con tonos profundos. Configuracion personalizada:

```css
@plugin "daisyui/theme" {
  name: "abyss";
  default: false;
  prefersdark: true;
  color-scheme: dark;

  --color-base-100: oklch(15% 0.02 260);
  --color-base-200: oklch(12% 0.02 260);
  --color-base-300: oklch(10% 0.02 260);
  --color-base-content: oklch(90% 0.02 260);
  --color-primary: oklch(65% 0.25 260);
  --color-primary-content: oklch(98% 0.01 260);
  --color-secondary: oklch(60% 0.2 300);
  --color-secondary-content: oklch(98% 0.01 300);
  --color-accent: oklch(70% 0.2 200);
  --color-accent-content: oklch(98% 0.01 200);
  --color-neutral: oklch(25% 0.02 260);
  --color-neutral-content: oklch(90% 0.01 260);
  --color-info: oklch(70% 0.2 220);
  --color-info-content: oklch(98% 0.01 220);
  --color-success: oklch(65% 0.25 140);
  --color-success-content: oklch(98% 0.01 140);
  --color-warning: oklch(80% 0.25 80);
  --color-warning-content: oklch(20% 0.05 80);
  --color-error: oklch(65% 0.3 30);
  --color-error-content: oklch(98% 0.01 30);

  --radius-selector: 0.5rem;
  --radius-field: 0.25rem;
  --radius-box: 0.5rem;
  --size-selector: 0.25rem;
  --size-field: 0.25rem;
  --border: 1px;
  --depth: 1;
  --noise: 0;
}
```

### Tema Light Alternativo: corporate

Tema profesional y corporativo para entornos empresariales.

### Tema Dark Alternativo: sunset

Tema oscuro con tonos calidos. Configuracion personalizada:

```css
@plugin "daisyui/theme" {
  name: "sunset";
  default: false;
  prefersdark: false;
  color-scheme: dark;

  --color-base-100: oklch(20% 0.03 30);
  --color-base-200: oklch(17% 0.03 30);
  --color-base-300: oklch(14% 0.03 30);
  --color-base-content: oklch(90% 0.02 50);
  --color-primary: oklch(70% 0.25 30);
  --color-primary-content: oklch(98% 0.01 30);
  --color-secondary: oklch(65% 0.2 60);
  --color-secondary-content: oklch(98% 0.01 60);
  --color-accent: oklch(75% 0.2 80);
  --color-accent-content: oklch(20% 0.05 80);
  --color-neutral: oklch(30% 0.03 30);
  --color-neutral-content: oklch(90% 0.01 30);
  --color-info: oklch(70% 0.2 220);
  --color-info-content: oklch(98% 0.01 220);
  --color-success: oklch(65% 0.25 140);
  --color-success-content: oklch(98% 0.01 140);
  --color-warning: oklch(80% 0.25 80);
  --color-warning-content: oklch(20% 0.05 80);
  --color-error: oklch(65% 0.3 0);
  --color-error-content: oklch(98% 0.01 0);

  --radius-selector: 0.5rem;
  --radius-field: 0.25rem;
  --radius-box: 0.5rem;
  --size-selector: 0.25rem;
  --size-field: 0.25rem;
  --border: 1px;
  --depth: 1;
  --noise: 0;
}
```

---

## Colores Semanticos DaisyUI

Los colores semanticos permiten que la UI se adapte automaticamente al tema:

| Color | Uso |
|-------|-----|
| `primary` | Color principal de marca, elementos importantes |
| `secondary` | Color secundario de marca |
| `accent` | Color de acento para destacar |
| `neutral` | Partes no saturadas de la UI |
| `base-100/200/300` | Fondos y elevaciones |
| `info` | Mensajes informativos |
| `success` | Mensajes de exito |
| `warning` | Mensajes de advertencia |
| `error` | Mensajes de error |

Cada color tiene su variante `-content` para texto/iconos sobre ese color.

---

## Reglas de Uso

1. SIEMPRE usar colores semanticos de DaisyUI, NO colores Tailwind directos
2. NO usar `dark:` - los colores se adaptan automaticamente
3. Usar `base-*` para la mayoria de la pagina
4. Usar `primary` para elementos importantes
5. Los colores `*-content` garantizan contraste legible

---

## Deteccion y Persistencia de Temas

### Estrategia de Deteccion (Prioridad)

1. **localStorage** - Preferencia guardada en navegador (persistente)
2. **session** - Cookie de sesion PHP (fallback servidor)
3. **Sistema operativo** - `prefers-color-scheme: dark` (default)

### Implementacion: Script Bloqueante

El tema se detecta ANTES del renderizado para evitar flash de tema incorrecto.
Este script debe estar en `<head>` antes de cualquier CSS:

```html
<script>
    (function() {
        const LIGHT_THEME = 'cupcake';
        const DARK_THEME = 'abyss';
        const savedTheme = localStorage.getItem('theme') || '{{ session('theme') }}';

        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
        } else {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', prefersDark ? DARK_THEME : LIGHT_THEME);
        }
    })();
</script>
```

### Toggle de Tema

```javascript
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const isDark = currentTheme === 'abyss' || currentTheme === 'sunset';
    const newTheme = isDark ? 'cupcake' : 'abyss';

    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);

    // Opcional: persistir en servidor
    fetch('/api/theme', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ theme: newTheme })
    });
}
```

### Archivos con Deteccion de Tema

- `resources/views/welcome.blade.php` - Landing page
- `resources/views/components/layouts/guest.blade.php` - Login/registro
- `resources/views/components/layouts/app.blade.php` - Aplicacion autenticada

### Regla Importante

**NO** usar `data-theme` por defecto en el tag `<html>`. El script bloqueante
se encarga de asignar el tema correcto basado en la preferencia del usuario
o del sistema operativo.

---

## Referencias

- DaisyUI Theme Generator: https://daisyui.com/theme-generator/
- DaisyUI Colors: https://daisyui.com/docs/colors/
- DaisyUI Themes: https://daisyui.com/docs/themes/
