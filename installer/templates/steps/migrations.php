<?php
/**
 * Migrations step template
 *
 * @var bool $hasTables
 * @var bool $recommendFresh
 */
?>
<div id="step-migrations">
    <h2 class="text-2xl font-bold text-white mb-2"><?= __('migrations.title') ?></h2>
    <p class="text-slate-400 mb-6"><?= __('migrations.description') ?></p>
    
    <form id="step-form" class="space-y-6">
        
        <div class="bg-slate-700/50 rounded-lg p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-brand-600 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-medium"><?= __('migrations.database_tables') ?></h3>
                    <p class="text-slate-400 text-sm"><?= __('migrations.will_create') ?></p>
                </div>
            </div>
            
            <?php if ($hasTables) { ?>
                <div class="bg-amber-900/30 border border-amber-500/50 rounded-lg p-4 mb-4">
                    <p class="text-amber-300">
                        <strong><?= __('migrations.tables_exist') ?></strong><br>
                        <?= __('migrations.fresh_warning') ?>
                    </p>
                </div>
                
                <label class="flex items-center gap-3 cursor-pointer mb-4">
                    <input 
                        type="checkbox" 
                        name="fresh" 
                        value="1"
                        <?= $recommendFresh ? 'checked' : '' ?>
                        class="w-5 h-5 rounded bg-slate-600 border-slate-500 text-amber-600 focus:ring-amber-500"
                    >
                    <span class="text-slate-300"><?= __('migrations.fresh_option') ?></span>
                </label>
            <?php } else { ?>
                <div class="bg-emerald-900/30 border border-emerald-500/50 rounded-lg p-4 mb-4">
                    <p class="text-emerald-300"><?= __('migrations.will_create_tables') ?></p>
                </div>
            <?php } ?>
            
            <label class="flex items-center gap-3 cursor-pointer">
                <input 
                    type="checkbox" 
                    name="run_seeders" 
                    value="1"
                    class="w-5 h-5 rounded bg-slate-600 border-slate-500 text-brand-600 focus:ring-brand-500"
                >
                <span class="text-slate-300"><?= __('migrations.run_seeders') ?></span>
            </label>
        </div>
        
        <!-- Tables to create -->
        <div class="bg-slate-700/50 rounded-lg p-4">
            <h4 class="text-white font-medium mb-3"><?= __('migrations.tables_created') ?></h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm text-slate-400">
                <span>users</span>
                <span>user_preferences</span>
                <span>user_tax_profiles</span>
                <span>company_fiscal_configs</span>
                <span>customer_fiscal_data</span>
                <span>invoices</span>
                <span>invoice_lines</span>
                <span>tax_rates</span>
                <span>tax_groups</span>
                <span>unit_measures</span>
                <span>legal_entity_types</span>
                <span>articles</span>
                <span>tickets</span>
                <span>settings</span>
                <span>jobs</span>
                <span>sessions</span>
            </div>
        </div>
        
    </form>
</div>

