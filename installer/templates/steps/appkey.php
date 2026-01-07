<?php
/**
 * AppKey step template
 *
 * @var bool $keyExists
 * @var string|null $currentKey
 */
?>
<div id="step-appkey">
    <h2 class="text-2xl font-bold text-white mb-2"><?= __('appkey.title') ?></h2>
    <p class="text-slate-400 mb-6"><?= __('appkey.description') ?></p>
    
    <form id="step-form" class="space-y-6">
        
        <div class="bg-slate-700/50 rounded-lg p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-brand-600 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-medium"><?= __('appkey.encryption_key') ?></h3>
                    <p class="text-slate-400 text-sm"><?= __('appkey.security_note') ?></p>
                </div>
            </div>
            
            <?php if ($keyExists) { ?>
                <div class="bg-emerald-900/30 border border-emerald-500/50 rounded-lg p-4 mb-4">
                    <p class="text-emerald-300 mb-2"><?= __('appkey.key_exists') ?></p>
                    <code class="text-sm text-slate-300 bg-slate-900 px-2 py-1 rounded"><?= $currentKey ?></code>
                </div>
                
                <label class="flex items-center gap-3 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="force_regenerate" 
                        value="1"
                        class="w-5 h-5 rounded bg-slate-600 border-slate-500 text-brand-600 focus:ring-brand-500"
                    >
                    <span class="text-slate-300"><?= __('appkey.regenerate') ?></span>
                </label>
            <?php } else { ?>
                <div class="bg-amber-900/30 border border-amber-500/50 rounded-lg p-4">
                    <p class="text-amber-300"><?= __('appkey.will_generate') ?></p>
                </div>
            <?php } ?>
        </div>
        
        <div class="bg-blue-900/30 border border-blue-500/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-blue-200 text-sm">
                    <strong>Importante:</strong> Esta clave se usa para encriptar todos los datos sensibles de la aplicación. 
                    Si ya tiene datos encriptados, cambiar la clave los hará inaccesibles.
                </div>
            </div>
        </div>
        
    </form>
</div>

