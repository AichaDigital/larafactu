<?php
/**
 * Welcome step template
 *
 * @var array $languages
 * @var string $currentLanguage
 */
?>
<div id="step-welcome">
    <div class="text-center">
        <div class="w-24 h-24 mx-auto bg-gradient-to-br from-brand-500 to-brand-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
            <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        
        <h2 class="text-3xl font-bold text-white mb-3"><?= __('welcome.title') ?></h2>
        <p class="text-slate-400 text-lg mb-8"><?= __('welcome.description') ?></p>
    </div>
    
    <form id="step-form" class="max-w-md mx-auto space-y-6">
        
        <!-- Language Selection -->
        <div class="bg-slate-700/50 rounded-xl p-6">
            <label class="block text-sm font-medium text-slate-300 mb-4 text-center">
                <?= __('welcome.language_select') ?>
            </label>
            
            <div class="grid grid-cols-2 gap-4">
                <?php foreach ($languages as $code => $name) { ?>
                <label class="relative">
                    <input 
                        type="radio" 
                        name="language" 
                        value="<?= $code ?>"
                        <?= $code === $currentLanguage ? 'checked' : '' ?>
                        class="peer sr-only"
                    >
                    <div class="p-4 rounded-lg bg-slate-600/50 border-2 border-transparent peer-checked:border-brand-500 peer-checked:bg-brand-900/30 cursor-pointer hover:bg-slate-600 transition text-center">
                        <span class="text-2xl mb-1 block"><?= $code === 'es' ? '🇪🇸' : '🇬🇧' ?></span>
                        <span class="text-white font-medium"><?= $name ?></span>
                    </div>
                </label>
                <?php } ?>
            </div>
        </div>
        
        <!-- What will be installed -->
        <div class="bg-slate-700/50 rounded-xl p-6">
            <h3 class="text-white font-medium mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <?= $currentLanguage === 'es' ? 'El asistente configurará:' : 'The wizard will configure:' ?>
            </h3>
            
            <ul class="space-y-2 text-slate-300 text-sm">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?= $currentLanguage === 'es' ? 'Verificación de requisitos del sistema' : 'System requirements check' ?>
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?= $currentLanguage === 'es' ? 'Clave de encriptación de la aplicación' : 'Application encryption key' ?>
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?= $currentLanguage === 'es' ? 'Conexión y estructura de base de datos' : 'Database connection and structure' ?>
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?= $currentLanguage === 'es' ? 'Datos fiscales de la empresa' : 'Company fiscal data' ?>
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?= $currentLanguage === 'es' ? 'Integración con Verifactu (opcional)' : 'Verifactu integration (optional)' ?>
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?= $currentLanguage === 'es' ? 'Usuario administrador' : 'Administrator user' ?>
                </li>
            </ul>
        </div>
        
        <!-- Start button info -->
        <div class="bg-blue-900/30 border border-blue-500/50 rounded-lg p-4 text-center">
            <p class="text-blue-200 text-sm">
                <?= $currentLanguage === 'es'
                    ? 'Haga clic en "Siguiente" para comenzar la instalación.'
                    : 'Click "Next" to start the installation.' ?>
            </p>
        </div>
        
    </form>
</div>

<style>
    .bg-brand-500 { background-color: #0ea5e9; }
    .bg-brand-700 { background-color: #0369a1; }
    .text-brand-400 { color: #38bdf8; }
    .peer-checked\:border-brand-500:checked ~ div { border-color: #0ea5e9; }
    .peer-checked\:bg-brand-900\/30:checked ~ div { background-color: rgba(12, 74, 110, 0.3); }
</style>
