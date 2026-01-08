<?php
/**
 * Dependencies step template
 *
 * @var bool $vendor_exists
 * @var bool $composer_available
 * @var string|null $composer_command
 * @var bool $artisan_works
 * @var string $larafactu_root
 */
?>
<div id="step-dependencies">
    <h2 class="text-2xl font-bold text-white mb-2"><?= __('dependencies.title') ?? 'Instalación de dependencias' ?></h2>
    <p class="text-slate-400 mb-6"><?= __('dependencies.description') ?? 'Se instalarán Laravel y todas las dependencias del proyecto' ?></p>
    
    <form id="step-form" class="space-y-6">
        
        <!-- Status Check -->
        <div class="bg-slate-700/50 rounded-lg p-4 space-y-3">
            <h3 class="font-medium text-white mb-3"><?= __('dependencies.status') ?? 'Estado actual' ?></h3>
            
            <!-- Composer Available -->
            <div class="flex items-center justify-between">
                <span class="text-slate-300">Composer</span>
                <?php if ($composer_available) { ?>
                    <span class="flex items-center gap-2 text-green-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?= __('dependencies.available') ?? 'Disponible' ?>
                    </span>
                <?php } else { ?>
                    <span class="flex items-center gap-2 text-red-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <?= __('dependencies.not_available') ?? 'No disponible' ?>
                    </span>
                <?php } ?>
            </div>
            
            <!-- Vendor Directory -->
            <div class="flex items-center justify-between">
                <span class="text-slate-300"><?= __('dependencies.vendor_dir') ?? 'Directorio vendor/' ?></span>
                <?php if ($vendor_exists) { ?>
                    <span class="flex items-center gap-2 text-green-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?= __('dependencies.exists') ?? 'Existe' ?>
                    </span>
                <?php } else { ?>
                    <span class="flex items-center gap-2 text-amber-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <?= __('dependencies.not_exists') ?? 'No existe' ?>
                    </span>
                <?php } ?>
            </div>
            
            <!-- Artisan Works -->
            <div class="flex items-center justify-between">
                <span class="text-slate-300">Laravel (artisan)</span>
                <?php if ($artisan_works) { ?>
                    <span class="flex items-center gap-2 text-green-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?= __('dependencies.working') ?? 'Funcionando' ?>
                    </span>
                <?php } else { ?>
                    <span class="flex items-center gap-2 text-amber-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <?= __('dependencies.not_working') ?? 'Pendiente' ?>
                    </span>
                <?php } ?>
            </div>
        </div>
        
        <?php if (! $composer_available) { ?>
            <!-- Composer Not Available Warning -->
            <div class="bg-red-900/30 border border-red-500/50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-red-200">
                        <strong><?= __('dependencies.composer_required') ?? 'Composer es necesario' ?></strong>
                        <p class="text-sm mt-1">
                            <?= __('dependencies.composer_install_hint') ?? 'Instale Composer desde <a href="https://getcomposer.org" target="_blank" class="underline">getcomposer.org</a> antes de continuar.' ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php } elseif ($vendor_exists && $artisan_works) { ?>
            <!-- Already Installed -->
            <div class="bg-green-900/30 border border-green-500/50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-green-200">
                        <strong><?= __('dependencies.already_installed') ?? 'Dependencias ya instaladas' ?></strong>
                        <p class="text-sm mt-1">
                            <?= __('dependencies.skip_info') ?? 'Las dependencias ya están instaladas. Puede continuar al siguiente paso.' ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Force Reinstall Option -->
            <label class="flex items-center gap-3 cursor-pointer">
                <input 
                    type="checkbox" 
                    name="force_install" 
                    value="1"
                    class="w-5 h-5 rounded bg-slate-600 border-slate-500 text-brand-600 focus:ring-brand-500"
                >
                <span class="text-slate-300"><?= __('dependencies.force_reinstall') ?? 'Forzar reinstalación de dependencias' ?></span>
            </label>
        <?php } else { ?>
            <!-- Will Install -->
            <div class="bg-blue-900/30 border border-blue-500/50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-blue-200">
                        <strong><?= __('dependencies.will_install') ?? 'Se instalarán las dependencias' ?></strong>
                        <p class="text-sm mt-1">
                            <?= __('dependencies.install_info') ?? 'Se ejecutará <code class="bg-slate-700 px-1 rounded">composer install</code> para instalar Laravel y todas las dependencias. Este proceso puede tardar unos minutos.' ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php } ?>
        
        <!-- Dev Dependencies Option -->
        <?php if ($composer_available && (! $vendor_exists || ! $artisan_works)) { ?>
            <label class="flex items-center gap-3 cursor-pointer">
                <input 
                    type="checkbox" 
                    name="install_dev" 
                    value="1"
                    class="w-5 h-5 rounded bg-slate-600 border-slate-500 text-brand-600 focus:ring-brand-500"
                >
                <div>
                    <span class="text-slate-300"><?= __('dependencies.install_dev') ?? 'Instalar dependencias de desarrollo' ?></span>
                    <p class="text-xs text-slate-500"><?= __('dependencies.install_dev_hint') ?? 'Solo necesario si va a desarrollar o ejecutar tests' ?></p>
                </div>
            </label>
        <?php } ?>
        
        <!-- Time Warning -->
        <?php if ($composer_available && ! $artisan_works) { ?>
            <div class="bg-amber-900/30 border border-amber-500/50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-amber-200 text-sm">
                        <strong><?= __('dependencies.time_warning') ?? 'Este paso puede tardar varios minutos' ?></strong>
                        <p class="mt-1">
                            <?= __('dependencies.time_info') ?? 'Dependiendo de su conexión a internet y servidor, la instalación puede tardar entre 1 y 5 minutos. No cierre esta ventana.' ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php } ?>
        
    </form>
</div>

