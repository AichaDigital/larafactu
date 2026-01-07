<?php
/**
 * Requirements step template
 *
 * @var array $results
 * @var bool $allPassed
 */
?>
<div id="step-requirements">
    <h2 class="text-2xl font-bold text-white mb-2"><?= __('requirements.title') ?></h2>
    <p class="text-slate-400 mb-6"><?= __('requirements.checking') ?></p>
    
    <form id="step-form" class="space-y-4">
        
        <!-- PHP Version -->
        <div class="bg-slate-700/50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <?php
                    $php = $results['php_version'] ?? [];
$icon = match ($php['status'] ?? 'error') {
    'ok' => '<svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
    'warning' => '<svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    default => '<svg class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
};
?>
                    <?= $icon ?>
                    <div>
                        <p class="text-white font-medium"><?= __('requirements.php_version') ?></p>
                        <p class="text-slate-400 text-sm"><?= $php['message'] ?? '' ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Extensions -->
        <div class="bg-slate-700/50 rounded-lg p-4">
            <div class="flex items-center gap-3 mb-3">
                <?php
                $ext = $results['extensions'] ?? [];
$icon = match ($ext['status'] ?? 'error') {
    'ok' => '<svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
    'warning' => '<svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    default => '<svg class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
};
?>
                <?= $icon ?>
                <div>
                    <p class="text-white font-medium"><?= __('requirements.extensions') ?></p>
                    <p class="text-slate-400 text-sm"><?= $ext['message'] ?? '' ?></p>
                </div>
            </div>
            
            <?php if (! empty($ext['details'])) { ?>
            <div class="pl-9 grid grid-cols-2 sm:grid-cols-3 gap-2">
                <?php
$allExtensions = array_merge(
    $ext['details']['loaded'] ?? [],
    $ext['details']['missing'] ?? [],
    $ext['details']['warnings'] ?? []
);
                foreach ($allExtensions as $name => $info) {
                    $color = match ($info['status'] ?? 'error') {
                        'ok' => 'text-emerald-400',
                        'warning' => 'text-amber-400',
                        default => 'text-red-400',
                    };
                    ?>
                <span class="text-sm <?= $color ?>"><?= $name ?></span>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        
        <!-- Writable Paths -->
        <div class="bg-slate-700/50 rounded-lg p-4">
            <div class="flex items-center gap-3 mb-3">
                <?php
                    $paths = $results['writable_paths'] ?? [];
$icon = match ($paths['status'] ?? 'error') {
    'ok' => '<svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
    'warning' => '<svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    default => '<svg class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
};
?>
                <?= $icon ?>
                <div>
                    <p class="text-white font-medium"><?= __('requirements.writable_paths') ?></p>
                    <p class="text-slate-400 text-sm"><?= $paths['message'] ?? '' ?></p>
                </div>
            </div>
            
            <?php if (! empty($paths['details'])) { ?>
            <div class="pl-9 space-y-1">
                <?php
$allPaths = array_merge($paths['details']['passed'] ?? [], $paths['details']['errors'] ?? []);
                foreach ($allPaths as $path => $info) {
                    $color = ($info['status'] ?? 'error') === 'ok' ? 'text-emerald-400' : 'text-red-400';
                    ?>
                <div class="flex items-center gap-2 text-sm">
                    <span class="<?= $color ?>"><?= $path ?></span>
                    <span class="text-slate-500"><?= $info['message'] ?? '' ?></span>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        
        <?php if (! $allPassed) { ?>
        <div class="bg-red-900/30 border border-red-500/50 rounded-lg p-4">
            <p class="text-red-300">
                <strong><?= __('requirements.some_failed') ?></strong><br>
                <?= __('requirements.fix_issues') ?>
            </p>
        </div>
        <?php } else { ?>
        <div class="bg-emerald-900/30 border border-emerald-500/50 rounded-lg p-4">
            <p class="text-emerald-300"><?= __('requirements.all_passed') ?></p>
        </div>
        <?php } ?>
        
    </form>
</div>

