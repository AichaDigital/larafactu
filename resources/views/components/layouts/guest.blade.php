<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="group/html">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Larafactu') }}</title>

    <!-- Theme detection: MUST run before any render to avoid flash -->
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

            // Listen for system theme changes (only if user hasn't set preference)
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!localStorage.getItem('theme') && !'{{ session('theme') }}') {
                    document.documentElement.setAttribute('data-theme', e.matches ? DARK_THEME : LIGHT_THEME);
                }
            });
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="min-h-screen font-sans antialiased">
    <div class="grid grid-cols-12 overflow-auto sm:min-h-screen">
        <!-- Left side - decorative (hidden on mobile) -->
        <div class="relative hidden bg-base-200 lg:col-span-7 lg:block xl:col-span-8">
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center p-8">
                    <h1 class="text-4xl font-bold text-primary mb-4">{{ config('app.name', 'Larafactu') }}</h1>
                    <p class="text-base-content/70 text-lg max-w-md">
                        Facturacion electronica simplificada para empresas espanolas
                    </p>
                </div>
            </div>
        </div>

        <!-- Right side - form -->
        <div class="col-span-12 lg:col-span-5 xl:col-span-4 bg-base-100">
            <div class="flex flex-col items-stretch p-6 md:p-8 lg:p-12 min-h-screen lg:min-h-0">
                <!-- Header with logo and theme toggle -->
                <div class="flex items-center justify-between">
                    <a href="/" class="text-xl font-bold text-primary">
                        {{ config('app.name', 'Larafactu') }}
                    </a>
                    <button
                        type="button"
                        id="theme-toggle-btn"
                        aria-label="Cambiar tema"
                        class="btn btn-circle btn-outline btn-sm border-base-300"
                        onclick="toggleTheme()"
                    >
                        <!-- Sun icon (shown in dark mode) -->
                        <svg id="sun-icon" class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
                        </svg>
                        <!-- Moon icon (shown in light mode) -->
                        <svg id="moon-icon" class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 flex flex-col justify-center mt-8 lg:mt-0">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    <script>
        const LIGHT_THEME = 'cupcake';
        const DARK_THEME = 'abyss';

        function isDarkTheme(theme) {
            return theme === 'abyss' || theme === 'sunset';
        }

        function updateThemeIcons() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const isDark = isDarkTheme(currentTheme);
            const sunIcon = document.getElementById('sun-icon');
            const moonIcon = document.getElementById('moon-icon');

            if (sunIcon && moonIcon) {
                sunIcon.style.display = isDark ? 'block' : 'none';
                moonIcon.style.display = isDark ? 'none' : 'block';
            }
        }

        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = isDarkTheme(currentTheme) ? LIGHT_THEME : DARK_THEME;

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcons();

            // Persist to server (optional, for server-side consistency)
            fetch('/api/theme', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ theme: newTheme })
            }).catch(() => {}); // Silently fail if API not available
        }

        // Initialize icons on load
        document.addEventListener('DOMContentLoaded', updateThemeIcons);
    </script>

    @livewireScripts
</body>
</html>
