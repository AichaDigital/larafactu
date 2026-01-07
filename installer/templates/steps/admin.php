<?php
/**
 * Admin step template
 *
 * @var int $minPasswordLength
 */
?>
<div id="step-admin">
    <h2 class="text-2xl font-bold text-white mb-2"><?= __('admin.title') ?></h2>
    <p class="text-slate-400 mb-6"><?= __('admin.description') ?></p>
    
    <form id="step-form" class="space-y-6">
        
        <div class="bg-slate-700/50 rounded-lg p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-16 h-16 bg-brand-600 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-medium text-lg"><?= __('admin.superadmin') ?></h3>
                    <p class="text-slate-400 text-sm"><?= __('admin.superadmin_note') ?></p>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('admin.name') ?> <span class="text-red-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="Administrador"
                        required
                    >
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('admin.email') ?> <span class="text-red-400">*</span>
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="admin@miempresa.com"
                        required
                    >
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('admin.password') ?> <span class="text-red-400">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        minlength="<?= $minPasswordLength ?>"
                        required
                    >
                    <p class="text-xs text-slate-500 mt-1"><?= __('admin.password_requirements') ?></p>
                </div>
                
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('admin.password_confirm') ?> <span class="text-red-400">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="password_confirm" 
                        id="password_confirm"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        required
                    >
                </div>
            </div>
        </div>
        
        <div class="bg-blue-900/30 border border-blue-500/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <div class="text-blue-200 text-sm">
                    <strong><?= __('admin.security_note') ?></strong><br>
                    <?= __('admin.security_tip') ?>
                </div>
            </div>
        </div>
        
    </form>
</div>

