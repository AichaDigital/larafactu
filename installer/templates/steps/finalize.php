<?php
/**
 * Finalize step template
 *
 * @var array $summary
 * @var string $appUrl
 */
?>
<div id="step-finalize">
    <div class="text-center mb-8">
        <div class="w-20 h-20 mx-auto bg-emerald-600 rounded-full flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-white mb-2"><?= __('finalize.title') ?></h2>
        <p class="text-slate-400"><?= __('finalize.description') ?></p>
    </div>
    
    <form id="step-form" class="space-y-6">
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Company -->
            <div class="bg-slate-700/50 rounded-lg p-4">
                <h3 class="text-white font-medium mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <?= __('finalize.company') ?>
                </h3>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-400"><?= __('company.business_name') ?>:</dt>
                        <dd class="text-white"><?= htmlspecialchars($summary['company']['name'] ?? '') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400"><?= __('company.tax_id') ?>:</dt>
                        <dd class="text-white font-mono"><?= htmlspecialchars($summary['company']['tax_id'] ?? '') ?></dd>
                    </div>
                    <?php if ($summary['company']['is_roi'] ?? false) { ?>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">ROI/OSS:</dt>
                        <dd class="text-emerald-400"><?= __('common.yes') ?></dd>
                    </div>
                    <?php } ?>
                </dl>
            </div>
            
            <!-- Admin -->
            <div class="bg-slate-700/50 rounded-lg p-4">
                <h3 class="text-white font-medium mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <?= __('finalize.admin') ?>
                </h3>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-400"><?= __('admin.name') ?>:</dt>
                        <dd class="text-white"><?= htmlspecialchars($summary['admin']['name'] ?? '') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400"><?= __('admin.email') ?>:</dt>
                        <dd class="text-white"><?= htmlspecialchars($summary['admin']['email'] ?? '') ?></dd>
                    </div>
                </dl>
            </div>
            
            <!-- Database -->
            <div class="bg-slate-700/50 rounded-lg p-4">
                <h3 class="text-white font-medium mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    <?= __('finalize.database') ?>
                </h3>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-400"><?= __('database.host') ?>:</dt>
                        <dd class="text-white font-mono"><?= htmlspecialchars($summary['database']['host'] ?? '') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400"><?= __('database.name') ?>:</dt>
                        <dd class="text-white font-mono"><?= htmlspecialchars($summary['database']['database'] ?? '') ?></dd>
                    </div>
                </dl>
            </div>
            
            <!-- Verifactu -->
            <div class="bg-slate-700/50 rounded-lg p-4">
                <h3 class="text-white font-medium mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Verifactu
                </h3>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-400"><?= __('verifactu.mode_label') ?>:</dt>
                        <dd class="<?= ($summary['verifactu']['configured'] ?? false) ? 'text-emerald-400' : 'text-amber-400' ?>">
                            <?= ucfirst($summary['verifactu']['mode'] ?? 'disabled') ?>
                        </dd>
                    </div>
                    <?php if ($summary['verifactu']['environment'] ?? null) { ?>
                    <div class="flex justify-between">
                        <dt class="text-slate-400"><?= __('verifactu.environment_label') ?>:</dt>
                        <dd class="text-white"><?= ucfirst($summary['verifactu']['environment']) ?></dd>
                    </div>
                    <?php } ?>
                </dl>
            </div>
            
        </div>
        
        <!-- Application URL -->
        <div class="bg-emerald-900/30 border border-emerald-500/50 rounded-lg p-6 text-center">
            <h3 class="text-white font-medium mb-2"><?= __('finalize.app_ready') ?></h3>
            <a href="<?= htmlspecialchars($appUrl) ?>" class="text-emerald-400 hover:text-emerald-300 text-lg font-mono break-all">
                <?= htmlspecialchars($appUrl) ?>
            </a>
        </div>
        
        <!-- Warning -->
        <div class="bg-amber-900/30 border border-amber-500/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-amber-200 text-sm">
                    <strong><?= __('finalize.delete_warning_title') ?></strong><br>
                    <?= __('finalize.delete_warning') ?>
                </div>
            </div>
        </div>
        
    </form>
</div>

