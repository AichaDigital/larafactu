{{--
    Theme toggle component - simplified to light/dark toggle.
    ADR-005: Uses cupcake (light) and abyss (dark) as defaults.
    Respects system preference when no user preference is set.
--}}
<button
    wire:click="toggleTheme"
    class="btn btn-circle btn-outline border-base-300 relative overflow-hidden"
    aria-label="Cambiar tema"
    @theme-changed.window="document.documentElement.setAttribute('data-theme', $event.detail.theme)"
>
    {{-- Sun icon (shown in dark mode) --}}
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="18"
        height="18"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="absolute transition-all duration-300 {{ $this->isDark() ? 'translate-y-0 opacity-100' : '-translate-y-4 opacity-0' }}"
    >
        <circle cx="12" cy="12" r="4"/>
        <path d="M12 2v2"/>
        <path d="M12 20v2"/>
        <path d="m4.93 4.93 1.41 1.41"/>
        <path d="m17.66 17.66 1.41 1.41"/>
        <path d="M2 12h2"/>
        <path d="M20 12h2"/>
        <path d="m6.34 17.66-1.41 1.41"/>
        <path d="m19.07 4.93-1.41 1.41"/>
    </svg>

    {{-- Moon icon (shown in light mode) --}}
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="18"
        height="18"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="absolute transition-all duration-300 {{ $this->isDark() ? 'translate-y-4 opacity-0' : 'translate-y-0 opacity-100' }}"
    >
        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
    </svg>
</button>
