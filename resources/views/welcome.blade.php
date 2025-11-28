<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Larafactu - Modern Billing Platform for Hosting Companies</title>
    <meta name="description" content="Complete billing and invoicing platform for hosting companies with Spanish tax compliance (Verifactu AEAT).">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-gray-100 min-h-screen">
    <div id="app" x-data="larafactuApp()" x-cloak>
        <!-- Header -->
        <header class="container mx-auto px-4 py-6 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Larafactu</h1>
            </div>
            
            <!-- Language Switcher -->
            <button @click="toggleLanguage()" class="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">
                <span class="text-xl" x-text="currentLang === 'es' ? '🇪🇸' : '🇬🇧'"></span>
                <span class="text-sm font-medium" x-text="currentLang === 'es' ? 'Español' : 'English'"></span>
            </button>
        </header>

        <!-- Hero Section -->
        <section class="container mx-auto px-4 py-20 text-center">
            <div class="max-w-4xl mx-auto">
                <span class="inline-block px-4 py-2 bg-blue-500/10 text-blue-400 rounded-full text-sm font-medium mb-6">
                    <span x-text="t('badge')"></span>
                </span>
                
                <h2 class="text-5xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                    <span x-text="t('hero.title')"></span>
                </h2>
                
                <p class="text-xl text-gray-400 mb-12 max-w-2xl mx-auto" x-text="t('hero.subtitle')"></p>
                
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="/admin" class="px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-lg font-semibold transition-all transform hover:scale-105">
                        <span x-text="t('hero.cta_admin')"></span>
                    </a>
                    <a href="https://github.com/AichaDigital" target="_blank" class="px-8 py-4 bg-gray-800 hover:bg-gray-700 text-white rounded-lg font-semibold transition-colors">
                        <span x-text="t('hero.cta_github')"></span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section class="container mx-auto px-4 py-20">
            <h3 class="text-3xl font-bold text-center mb-12" x-text="t('features.title')"></h3>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <template x-for="feature in t('features.items')" :key="feature.title">
                    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6 hover:border-blue-500/50 transition-colors">
                        <div class="text-4xl mb-4" x-text="feature.icon"></div>
                        <h4 class="text-xl font-bold mb-3 text-white" x-text="feature.title"></h4>
                        <p class="text-gray-400" x-text="feature.description"></p>
                    </div>
                </template>
            </div>
        </section>

        <!-- Tech Stack -->
        <section class="container mx-auto px-4 py-20">
            <h3 class="text-3xl font-bold text-center mb-12" x-text="t('stack.title')"></h3>
            
            <div class="flex flex-wrap gap-6 justify-center max-w-4xl mx-auto">
                <template x-for="tech in t('stack.items')" :key="tech.name">
                    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-lg px-6 py-4 hover:border-purple-500/50 transition-colors">
                        <div class="text-center">
                            <div class="text-3xl mb-2" x-text="tech.icon"></div>
                            <div class="font-semibold text-white" x-text="tech.name"></div>
                            <div class="text-sm text-gray-400" x-text="tech.version"></div>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <!-- Packages -->
        <section class="container mx-auto px-4 py-20">
            <h3 class="text-3xl font-bold text-center mb-12" x-text="t('packages.title')"></h3>
            
            <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                <template x-for="pkg in t('packages.items')" :key="pkg.name">
                    <a :href="pkg.url" target="_blank" class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-xl p-6 hover:border-blue-500/50 transition-all transform hover:scale-[1.02]">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="text-lg font-bold text-white" x-text="pkg.name"></h4>
                            <span class="px-2 py-1 bg-blue-500/20 text-blue-400 text-xs rounded" x-text="pkg.status"></span>
                        </div>
                        <p class="text-gray-400 text-sm mb-4" x-text="pkg.description"></p>
                        <div class="flex items-center gap-2 text-blue-400 text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="currentLang === 'es' ? 'Ver en GitHub' : 'View on GitHub'"></span>
                        </div>
                    </a>
                </template>
            </div>
        </section>

        <!-- Footer -->
        <footer class="container mx-auto px-4 py-12 border-t border-gray-800">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-gray-400 text-sm text-center md:text-left">
                    <p x-text="t('footer.built_with')"></p>
                    <p class="mt-1" x-text="t('footer.version')"></p>
                </div>
                
                <div class="flex gap-6">
                    <a href="https://github.com/AichaDigital" target="_blank" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </footer>
    </div>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        function larafactuApp() {
            return {
                currentLang: 'es',
                
                translations: {
                    es: {
                        badge: 'v1.0 - Staging',
                        hero: {
                            title: 'Facturación Moderna para Empresas de Hosting',
                            subtitle: 'Plataforma completa de billing con cumplimiento fiscal español (Verifactu AEAT). Construida con Laravel, diseñada para hosting.',
                            cta_admin: '🚀 Panel Admin',
                            cta_github: '📦 GitHub'
                        },
                        features: {
                            title: 'Características Principales',
                            items: [
                                {
                                    icon: '🧾',
                                    title: 'Facturación Completa',
                                    description: 'Facturas, presupuestos, notas de crédito. Todo lo que necesitas para gestionar tu billing.'
                                },
                                {
                                    icon: '🇪🇺',
                                    title: 'ROI/OSS',
                                    description: 'Operador intracomunitario con reverse charge automático para operaciones B2B.'
                                },
                                {
                                    icon: '🏛️',
                                    title: 'Verifactu AEAT',
                                    description: 'Integración nativa con el sistema español de verificación fiscal automática.'
                                },
                                {
                                    icon: '💰',
                                    title: 'Base 100',
                                    description: 'Cálculos monetarios precisos sin errores de float. Precisión de centavo.'
                                },
                                {
                                    icon: '🎫',
                                    title: 'Sistema de Tickets',
                                    description: 'Soporte integrado con escalado, asignaciones y SLA tracking.'
                                },
                                {
                                    icon: '🔐',
                                    title: 'UUID v7 Seguro',
                                    description: 'Protección contra ataques de descubrimiento con IDs ordenados temporalmente.'
                                }
                            ]
                        },
                        stack: {
                            title: 'Stack Tecnológico',
                            items: [
                                { icon: '🐘', name: 'PHP', version: '8.4+' },
                                { icon: '🔴', name: 'Laravel', version: '12.x' },
                                { icon: '🎨', name: 'Filament', version: '4.x' },
                                { icon: '⚡', name: 'Livewire', version: '3.x' },
                                { icon: '🎯', name: 'Tailwind', version: '4.x' },
                                { icon: '🧪', name: 'Pest', version: '4.x' }
                            ]
                        },
                        packages: {
                            title: 'Paquetes Modulares',
                            items: [
                                {
                                    name: 'aichadigital/larabill',
                                    description: 'Core de facturación y billing. Facturas, clientes, productos, impuestos.',
                                    status: 'dev-main',
                                    url: 'https://github.com/AichaDigital/larabill'
                                },
                                {
                                    name: 'aichadigital/lararoi',
                                    description: 'Lógica fiscal ROI/OSS para operadores intracomunitarios en la UE.',
                                    status: 'dev-main',
                                    url: 'https://github.com/AichaDigital/lararoi'
                                },
                                {
                                    name: 'aichadigital/lara-verifactu',
                                    description: 'Integración completa con sistema Verifactu de la AEAT española.',
                                    status: 'dev-main',
                                    url: 'https://github.com/AichaDigital/lara-verifactu'
                                },
                                {
                                    name: 'aichadigital/laratickets',
                                    description: 'Sistema de tickets con escalado, asignaciones y SLA tracking.',
                                    status: 'dev-main',
                                    url: 'https://github.com/AichaDigital/laratickets'
                                }
                            ]
                        },
                        footer: {
                            built_with: '❤️ Desarrollado por Aicha Digital',
                            version: 'v1.0.0-staging • Target: 15 dic 2025'
                        }
                    },
                    en: {
                        badge: 'v1.0 - Staging',
                        hero: {
                            title: 'Modern Billing for Hosting Companies',
                            subtitle: 'Complete billing platform with Spanish tax compliance (Verifactu AEAT). Built with Laravel, designed for hosting.',
                            cta_admin: '🚀 Admin Panel',
                            cta_github: '📦 GitHub'
                        },
                        features: {
                            title: 'Key Features',
                            items: [
                                {
                                    icon: '🧾',
                                    title: 'Complete Invoicing',
                                    description: 'Invoices, quotes, credit notes. Everything you need to manage your billing.'
                                },
                                {
                                    icon: '🇪🇺',
                                    title: 'ROI/OSS',
                                    description: 'Intra-community operator with automatic reverse charge for B2B operations.'
                                },
                                {
                                    icon: '🏛️',
                                    title: 'Verifactu AEAT',
                                    description: 'Native integration with the Spanish automatic tax verification system.'
                                },
                                {
                                    icon: '💰',
                                    title: 'Base 100',
                                    description: 'Precise monetary calculations without float errors. Penny-perfect precision.'
                                },
                                {
                                    icon: '🎫',
                                    title: 'Ticket System',
                                    description: 'Integrated support with escalation, assignments, and SLA tracking.'
                                },
                                {
                                    icon: '🔐',
                                    title: 'Secure UUID v7',
                                    description: 'Protection against discovery attacks with time-ordered IDs.'
                                }
                            ]
                        },
                        stack: {
                            title: 'Tech Stack',
                            items: [
                                { icon: '🐘', name: 'PHP', version: '8.4+' },
                                { icon: '🔴', name: 'Laravel', version: '12.x' },
                                { icon: '🎨', name: 'Filament', version: '4.x' },
                                { icon: '⚡', name: 'Livewire', version: '3.x' },
                                { icon: '🎯', name: 'Tailwind', version: '4.x' },
                                { icon: '🧪', name: 'Pest', version: '4.x' }
                            ]
                        },
                        packages: {
                            title: 'Modular Packages',
                            items: [
                                {
                                    name: 'aichadigital/larabill',
                                    description: 'Billing and invoicing core. Invoices, customers, products, taxes.',
                                    status: 'dev-main',
                                    url: 'https://github.com/AichaDigital/larabill'
                                },
                                {
                                    name: 'aichadigital/lararoi',
                                    description: 'ROI/OSS tax logic for intra-community operators in the EU.',
                                    status: 'dev-main',
                                    url: 'https://github.com/AichaDigital/lararoi'
                                },
                                {
                                    name: 'aichadigital/lara-verifactu',
                                    description: 'Complete integration with Spanish AEAT Verifactu system.',
                                    status: 'dev-main',
                                    url: 'https://github.com/AichaDigital/lara-verifactu'
                                },
                                {
                                    name: 'aichadigital/laratickets',
                                    description: 'Ticket system with escalation, assignments, and SLA tracking.',
                                    status: 'dev-main',
                                    url: 'https://github.com/AichaDigital/laratickets'
                                }
                            ]
                        },
                        footer: {
                            built_with: '❤️ Built by Aicha Digital',
                            version: 'v1.0.0-staging • Target: Dec 15, 2025'
                        }
                    }
                },
                
                toggleLanguage() {
                    this.currentLang = this.currentLang === 'es' ? 'en' : 'es';
                    document.documentElement.lang = this.currentLang;
                },
                
                t(path) {
                    const keys = path.split('.');
                    let result = this.translations[this.currentLang];
                    
                    for (const key of keys) {
                        result = result[key];
                    }
                    
                    return result;
                }
            };
        }
    </script>
</body>
</html>
