<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Larafactu') }} - Facturacion Electronica</title>
    <meta name="description" content="Plataforma completa de facturacion electronica con cumplimiento fiscal espanol (Verifactu AEAT).">

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
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-100">
    <!-- Navbar -->
    <div class="navbar bg-base-100 border-b border-base-200">
        <div class="navbar-start">
            <a href="/" class="btn btn-ghost text-xl font-bold text-primary">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Larafactu
            </a>
        </div>
        <div class="navbar-end gap-2">
            <button
                type="button"
                id="theme-toggle"
                class="btn btn-ghost btn-circle"
                onclick="toggleTheme()"
                aria-label="Cambiar tema"
            >
                <svg id="sun-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                </svg>
                <svg id="moon-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                </svg>
            </button>
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost">Iniciar Sesion</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Registrarse</a>
            @endauth
        </div>
    </div>

    <!-- Hero -->
    <div class="hero min-h-[70vh] bg-base-200">
        <div class="hero-content text-center">
            <div class="max-w-3xl">
                <!-- Pre-production badge -->
                <div class="badge badge-warning badge-lg gap-2 mb-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Pre-Produccion / Staging
                </div>

                <h1 class="text-5xl font-bold text-base-content">Facturacion Electronica Moderna</h1>
                <p class="py-6 text-lg text-base-content/70">
                    Plataforma completa de billing con cumplimiento fiscal espanol (Verifactu AEAT).
                    Construida con Laravel, disenada para empresas de hosting.
                </p>
                <div class="flex flex-wrap gap-4 justify-center">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Ir al Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Comenzar Ahora</a>
                        <a href="{{ route('login') }}" class="btn btn-outline btn-lg">Iniciar Sesion</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="py-20 px-4 bg-base-100">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">Caracteristicas Principales</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="card bg-base-200">
                    <div class="card-body">
                        <div class="text-4xl mb-2">🧾</div>
                        <h3 class="card-title">Facturacion Completa</h3>
                        <p class="text-base-content/70">Facturas, presupuestos, notas de credito. Todo lo necesario para gestionar tu billing.</p>
                    </div>
                </div>
                <div class="card bg-base-200">
                    <div class="card-body">
                        <div class="text-4xl mb-2">🇪🇺</div>
                        <h3 class="card-title">ROI/OSS</h3>
                        <p class="text-base-content/70">Operador intracomunitario con reverse charge automatico para operaciones B2B.</p>
                    </div>
                </div>
                <div class="card bg-base-200">
                    <div class="card-body">
                        <div class="text-4xl mb-2">🏛️</div>
                        <h3 class="card-title">Verifactu AEAT</h3>
                        <p class="text-base-content/70">Integracion nativa con el sistema espanol de verificacion fiscal automatica.</p>
                    </div>
                </div>
                <div class="card bg-base-200">
                    <div class="card-body">
                        <div class="text-4xl mb-2">💰</div>
                        <h3 class="card-title">Base 100</h3>
                        <p class="text-base-content/70">Calculos monetarios precisos sin errores de float. Precision de centavo.</p>
                    </div>
                </div>
                <div class="card bg-base-200">
                    <div class="card-body">
                        <div class="text-4xl mb-2">🎫</div>
                        <h3 class="card-title">Sistema de Tickets</h3>
                        <p class="text-base-content/70">Soporte integrado con escalado, asignaciones y SLA tracking.</p>
                    </div>
                </div>
                <div class="card bg-base-200">
                    <div class="card-body">
                        <div class="text-4xl mb-2">🔐</div>
                        <h3 class="card-title">UUID v7 Seguro</h3>
                        <p class="text-base-content/70">Proteccion contra ataques de descubrimiento con IDs ordenados temporalmente.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tech Stack -->
    <div class="py-20 px-4 bg-base-200">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">Stack Tecnologico</h2>
            <div class="flex flex-wrap gap-4 justify-center">
                <div class="badge badge-lg badge-outline gap-2 p-4">🐘 PHP 8.4+</div>
                <div class="badge badge-lg badge-outline gap-2 p-4">🔴 Laravel 12</div>
                <div class="badge badge-lg badge-outline gap-2 p-4">⚡ Livewire 3</div>
                <div class="badge badge-lg badge-outline gap-2 p-4">🎯 Tailwind 4</div>
                <div class="badge badge-lg badge-outline gap-2 p-4">🌼 DaisyUI 5</div>
                <div class="badge badge-lg badge-outline gap-2 p-4">🧪 Pest 4</div>
            </div>
        </div>
    </div>

    <!-- Packages -->
    <div class="py-20 px-4 bg-base-100">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">Paquetes Modulares</h2>
            <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                <a href="https://github.com/AichaDigital/larabill" target="_blank" class="card bg-base-200 hover:bg-base-300 transition-colors">
                    <div class="card-body">
                        <div class="flex justify-between items-start">
                            <h3 class="card-title">aichadigital/larabill</h3>
                            <span class="badge badge-primary badge-sm">dev-main</span>
                        </div>
                        <p class="text-base-content/70">Core de facturacion y billing. Facturas, clientes, productos, impuestos.</p>
                    </div>
                </a>
                <a href="https://github.com/AichaDigital/lararoi" target="_blank" class="card bg-base-200 hover:bg-base-300 transition-colors">
                    <div class="card-body">
                        <div class="flex justify-between items-start">
                            <h3 class="card-title">aichadigital/lararoi</h3>
                            <span class="badge badge-primary badge-sm">dev-main</span>
                        </div>
                        <p class="text-base-content/70">Logica fiscal ROI/OSS para operadores intracomunitarios en la UE.</p>
                    </div>
                </a>
                <a href="https://github.com/AichaDigital/lara-verifactu" target="_blank" class="card bg-base-200 hover:bg-base-300 transition-colors">
                    <div class="card-body">
                        <div class="flex justify-between items-start">
                            <h3 class="card-title">aichadigital/lara-verifactu</h3>
                            <span class="badge badge-primary badge-sm">dev-main</span>
                        </div>
                        <p class="text-base-content/70">Integracion completa con sistema Verifactu de la AEAT espanola.</p>
                    </div>
                </a>
                <a href="https://github.com/AichaDigital/laratickets" target="_blank" class="card bg-base-200 hover:bg-base-300 transition-colors">
                    <div class="card-body">
                        <div class="flex justify-between items-start">
                            <h3 class="card-title">aichadigital/laratickets</h3>
                            <span class="badge badge-primary badge-sm">dev-main</span>
                        </div>
                        <p class="text-base-content/70">Sistema de tickets con escalado, asignaciones y SLA tracking.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer footer-center p-10 bg-base-200 text-base-content">
        <aside>
            <p class="font-bold">Aicha Digital</p>
            <p>Desarrollando soluciones de facturacion desde 2024</p>
            <p class="text-warning font-semibold">v1.0.0-staging - Lanzamiento estable: 15 dic 2025</p>
        </aside>
        <nav>
            <div class="grid grid-flow-col gap-4">
                <a href="https://github.com/AichaDigital" target="_blank" class="btn btn-ghost btn-circle">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                    </svg>
                </a>
            </div>
        </nav>
    </footer>

    <script>
        function isDarkTheme(theme) {
            return theme === 'abyss' || theme === 'sunset';
        }

        function updateThemeIcons() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const isDark = isDarkTheme(currentTheme);
            const sunIcon = document.getElementById('sun-icon');
            const moonIcon = document.getElementById('moon-icon');

            if (sunIcon && moonIcon) {
                sunIcon.classList.toggle('hidden', !isDark);
                moonIcon.classList.toggle('hidden', isDark);
            }
        }

        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = isDarkTheme(currentTheme) ? 'cupcake' : 'abyss';

            html.setAttribute('data-theme', newTheme);
            updateThemeIcons();

            // Persist to server
            fetch('/api/theme', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ theme: newTheme })
            });
        }

        document.addEventListener('DOMContentLoaded', updateThemeIcons);
    </script>
</body>
</html>
