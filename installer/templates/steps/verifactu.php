<?php
/**
 * Verifactu step template
 *
 * @var array $modes
 * @var array $environments
 * @var string $companyTaxId
 */
?>
<div id="step-verifactu" x-data="{ mode: 'disabled' }">
    <h2 class="text-2xl font-bold text-white mb-2"><?= __('verifactu.title') ?></h2>
    <p class="text-slate-400 mb-6"><?= __('verifactu.description') ?></p>
    
    <form id="step-form" class="space-y-6" enctype="multipart/form-data">
        
        <!-- Mode Selection -->
        <div class="bg-slate-700/50 rounded-lg p-4">
            <h3 class="text-white font-medium mb-4"><?= __('verifactu.mode_label') ?></h3>
            
            <div class="space-y-3">
                <?php foreach ($modes as $value => $label) { ?>
                <label class="flex items-center gap-3 p-3 rounded-lg bg-slate-600/50 hover:bg-slate-600 cursor-pointer transition">
                    <input 
                        type="radio" 
                        name="mode" 
                        value="<?= $value ?>"
                        x-model="mode"
                        <?= $value === 'disabled' ? 'checked' : '' ?>
                        class="w-5 h-5 text-brand-600 bg-slate-700 border-slate-500 focus:ring-brand-500"
                    >
                    <div>
                        <span class="text-white font-medium"><?= $label ?></span>
                        <?php if ($value === 'native') { ?>
                        <p class="text-sm text-slate-400"><?= __('verifactu.native_description') ?></p>
                        <?php } elseif ($value === 'api') { ?>
                        <p class="text-sm text-slate-400"><?= __('verifactu.api_description') ?></p>
                        <?php } else { ?>
                        <p class="text-sm text-slate-400"><?= __('verifactu.disabled_description') ?></p>
                        <?php } ?>
                    </div>
                </label>
                <?php } ?>
            </div>
        </div>
        
        <!-- Environment (shown when not disabled) -->
        <div x-show="mode !== 'disabled'" x-transition class="bg-slate-700/50 rounded-lg p-4">
            <h3 class="text-white font-medium mb-4"><?= __('verifactu.environment_label') ?></h3>
            
            <div class="grid grid-cols-2 gap-4">
                <?php foreach ($environments as $value => $label) { ?>
                <label class="flex items-center gap-3 p-4 rounded-lg bg-slate-600/50 hover:bg-slate-600 cursor-pointer transition border-2 border-transparent has-[:checked]:border-brand-500">
                    <input 
                        type="radio" 
                        name="environment" 
                        value="<?= $value ?>"
                        <?= $value === 'sandbox' ? 'checked' : '' ?>
                        class="w-5 h-5 text-brand-600 bg-slate-700 border-slate-500 focus:ring-brand-500"
                    >
                    <div>
                        <span class="text-white font-medium"><?= $label ?></span>
                        <?php if ($value === 'sandbox') { ?>
                        <p class="text-sm text-emerald-400"><?= __('verifactu.recommended') ?></p>
                        <?php } ?>
                    </div>
                </label>
                <?php } ?>
            </div>
        </div>
        
        <!-- Certificate Upload (shown for native mode) -->
        <div x-show="mode === 'native'" x-transition class="bg-slate-700/50 rounded-lg p-4">
            <h3 class="text-white font-medium mb-4"><?= __('verifactu.certificate') ?></h3>
            
            <div class="space-y-4">
                <div>
                    <label for="certificate" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('verifactu.certificate_file') ?> <span class="text-red-400">*</span>
                    </label>
                    <input 
                        type="file" 
                        name="certificate" 
                        id="certificate"
                        accept=".p12,.pfx,.pem,.cer,.crt"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-600 file:text-white hover:file:bg-brand-500"
                    >
                    <p class="text-xs text-slate-500 mt-1"><?= __('verifactu.certificate_formats') ?></p>
                </div>
                
                <div>
                    <label for="certificate_password" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('verifactu.certificate_password') ?> <span class="text-red-400">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="certificate_password" 
                        id="certificate_password"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    >
                </div>
                
                <div class="bg-amber-900/30 border border-amber-500/50 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="text-amber-200 text-sm">
                            <strong><?= __('verifactu.security_warning') ?></strong><br>
                            <?= __('verifactu.certificate_encrypted') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Company NIF for Verifactu -->
        <div x-show="mode !== 'disabled'" x-transition class="bg-slate-700/50 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-slate-300"><?= __('verifactu.nif_configured') ?></p>
                    <p class="text-white font-mono"><?= htmlspecialchars($companyTaxId) ?></p>
                </div>
            </div>
        </div>
        
        <!-- Skip warning -->
        <div x-show="mode === 'disabled'" x-transition class="bg-blue-900/30 border border-blue-500/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-blue-200 text-sm">
                    <?= __('verifactu.skip_warning') ?>
                </div>
            </div>
        </div>
        
    </form>
</div>

