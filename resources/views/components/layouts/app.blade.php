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
                if (!localStorage.getItem('theme')) {
                    document.documentElement.setAttribute('data-theme', e.matches ? DARK_THEME : LIGHT_THEME);
                }
            });
        })();
    </script>

    <!-- Fonts: Inter (similar to Nexus template) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="min-h-screen bg-base-200 antialiased">
    {{-- Impersonation bar --}}
    <x-impersonation-bar />

    {{-- Nexus-style layout with sidebar controls --}}
    <div class="size-full">
        <div class="flex">
            {{-- Sidebar toggle control (mobile + direct collapse) --}}
            <input
                type="checkbox"
                id="layout-sidebar-toggle-trigger"
                class="hidden"
                aria-label="Toggle layout sidebar" />

            {{-- Sidebar hover mode control (auto-hide with hover) --}}
            <input
                type="checkbox"
                id="layout-sidebar-hover-trigger"
                class="hidden"
                aria-label="Dense layout sidebar" />

            {{-- Hover detection element --}}
            <div id="layout-sidebar-hover" class="bg-base-300 h-screen w-1"></div>

            {{-- Sidebar --}}
            <x-sidebar />

            {{-- Backdrop for mobile --}}
            <label for="layout-sidebar-toggle-trigger" id="layout-sidebar-backdrop"></label>

            {{-- Main content area --}}
            <div class="flex h-screen min-w-0 grow flex-col overflow-auto">
                {{-- Navbar --}}
                <x-navbar />

                {{-- Page content --}}
                <main class="flex-1 p-4 lg:p-6">
                    {{ $slot }}
                </main>

                {{-- Footer --}}
                <footer class="footer footer-center bg-base-300 text-base-content p-4">
                    <aside>
                        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Larafactu') }}</p>
                    </aside>
                </footer>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
