<?php
/**
 * Database step template
 *
 * @var array $defaults
 */
?>
<div id="step-database">
    <h2 class="text-2xl font-bold text-white mb-2"><?= __('database.title') ?></h2>
    <p class="text-slate-400 mb-6"><?= __('database.description') ?></p>
    
    <form id="step-form" class="space-y-4">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Host -->
            <div>
                <label for="host" class="block text-sm font-medium text-slate-300 mb-1">
                    <?= __('database.host') ?> <span class="text-red-400">*</span>
                </label>
                <input 
                    type="text" 
                    name="host" 
                    id="host"
                    value="<?= htmlspecialchars($defaults['host'] ?? '127.0.0.1') ?>"
                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    required
                >
            </div>
            
            <!-- Port -->
            <div>
                <label for="port" class="block text-sm font-medium text-slate-300 mb-1">
                    <?= __('database.port') ?> <span class="text-red-400">*</span>
                </label>
                <input 
                    type="number" 
                    name="port" 
                    id="port"
                    value="<?= htmlspecialchars($defaults['port'] ?? '3306') ?>"
                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    required
                >
            </div>
        </div>
        
        <!-- Database Name -->
        <div>
            <label for="database" class="block text-sm font-medium text-slate-300 mb-1">
                <?= __('database.name') ?> <span class="text-red-400">*</span>
            </label>
            <input 
                type="text" 
                name="database" 
                id="database"
                value="<?= htmlspecialchars($defaults['database'] ?? 'larafactu') ?>"
                class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                required
            >
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-slate-300 mb-1">
                    <?= __('database.username') ?> <span class="text-red-400">*</span>
                </label>
                <input 
                    type="text" 
                    name="username" 
                    id="username"
                    value="<?= htmlspecialchars($defaults['username'] ?? 'root') ?>"
                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    required
                >
            </div>
            
            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1">
                    <?= __('database.password') ?>
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                >
            </div>
        </div>
        
        <!-- Create if not exists -->
        <label class="flex items-center gap-3 cursor-pointer">
            <input 
                type="checkbox" 
                name="create_if_not_exists" 
                value="1"
                checked
                class="w-5 h-5 rounded bg-slate-600 border-slate-500 text-brand-600 focus:ring-brand-500"
            >
            <span class="text-slate-300"><?= __('database.create_if_not_exists') ?></span>
        </label>
        
        <!-- ID Type Selection -->
        <div class="border-t border-slate-700 pt-4 mt-4">
            <label class="block text-sm font-medium text-slate-300 mb-3">
                <?= __('database.id_type') ?? 'Tipo de ID para usuarios y entidades' ?> <span class="text-red-400">*</span>
            </label>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- UUID Option -->
                <label class="relative flex items-start p-4 bg-slate-700/50 border-2 border-brand-500 rounded-lg cursor-pointer hover:bg-slate-700 transition-colors">
                    <input 
                        type="radio" 
                        name="id_type" 
                        value="uuid"
                        checked
                        class="sr-only"
                        onchange="this.closest('.grid').querySelectorAll('label').forEach(l => l.classList.remove('border-brand-500')); this.closest('label').classList.add('border-brand-500');"
                    >
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-white">UUID v7</span>
                            <span class="px-2 py-0.5 text-xs bg-brand-600 text-white rounded"><?= __('database.recommended') ?? 'Recomendado' ?></span>
                        </div>
                        <p class="text-sm text-slate-400">
                            <?= __('database.uuid_description') ?? 'Identificadores únicos universales. Más seguros, escalables y recomendados para nuevas instalaciones.' ?>
                        </p>
                    </div>
                </label>
                
                <!-- Integer Option -->
                <label class="relative flex items-start p-4 bg-slate-700/50 border-2 border-slate-600 rounded-lg cursor-pointer hover:bg-slate-700 transition-colors">
                    <input 
                        type="radio" 
                        name="id_type" 
                        value="integer"
                        class="sr-only"
                        onchange="this.closest('.grid').querySelectorAll('label').forEach(l => l.classList.remove('border-brand-500')); this.closest('label').classList.add('border-brand-500');"
                    >
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-white">Integer</span>
                            <span class="px-2 py-0.5 text-xs bg-slate-600 text-slate-300 rounded"><?= __('database.legacy') ?? 'Clásico' ?></span>
                        </div>
                        <p class="text-sm text-slate-400">
                            <?= __('database.integer_description') ?? 'IDs numéricos auto-incrementales. Compatible con migraciones desde sistemas legacy.' ?>
                        </p>
                    </div>
                </label>
            </div>
            
            <p class="mt-2 text-xs text-amber-400">
                <strong><?= __('database.warning') ?? 'Importante' ?>:</strong> 
                <?= __('database.id_type_warning') ?? 'Esta configuración NO se puede cambiar después de la instalación.' ?>
            </p>
        </div>
        
        <div class="bg-blue-900/30 border border-blue-500/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-blue-200 text-sm">
                    <strong>Nota:</strong> Asegúrese de que el usuario MySQL tiene permisos para crear bases de datos 
                    si la base de datos aún no existe.
                </div>
            </div>
        </div>
        
    </form>
</div>

