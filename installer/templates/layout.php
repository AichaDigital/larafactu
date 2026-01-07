<!DOCTYPE html>
<html lang="<?= e($translator->getLocale()) ?>" x-data="wizardApp()" x-init="init()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('wizard.title') ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('styles.css') ?>">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    
    <!-- Header -->
    <header class="bg-black/30 backdrop-blur-sm border-b border-white/10">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    <path d="M9 12h6M9 16h6"/>
                </svg>
                <span class="text-xl font-bold text-white"><?= __('wizard.title') ?></span>
            </div>
            
            <!-- Language Selector -->
            <div class="flex items-center gap-4">
                <span class="text-sm text-purple-300" x-show="remainingMinutes > 0">
                    ⏱️ <?= __('wizard.time_remaining', ['minutes' => $remainingMinutes]) ?>
                </span>
                <div class="flex gap-2">
                    <?php foreach ($translator->getSupportedLocales() as $locale) { ?>
                        <a href="?lang=<?= $locale ?>" 
                           class="px-3 py-1 rounded text-sm transition-colors <?= $translator->getLocale() === $locale
                               ? 'bg-purple-600 text-white'
                               : 'bg-white/10 text-gray-300 hover:bg-white/20' ?>">
                            <?= strtoupper($locale) ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Progress Bar -->
    <div class="max-w-4xl mx-auto px-4 mt-8">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-purple-300">
                <?= __('wizard.progress', ['current' => $currentStep, 'total' => 9]) ?>
            </span>
        </div>
        <div class="h-2 bg-white/10 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 transition-all duration-500"
                 :style="'width: ' + ((currentStep / totalSteps) * 100) + '%'"></div>
        </div>
        
        <!-- Step Indicators -->
        <div class="flex justify-between mt-4 overflow-x-auto pb-2">
            <?php
            $steps = ['welcome', 'requirements', 'appkey', 'database', 'migrations', 'company', 'verifactu', 'admin', 'finalize'];
foreach ($steps as $index => $stepId) {
    $stepNum = $index + 1;
    ?>
                <div class="flex flex-col items-center min-w-[60px]">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors
                        <?= $stepNum < $currentStep ? 'bg-green-500 text-white' : '' ?>
                        <?= $stepNum === $currentStep ? 'bg-purple-500 text-white ring-2 ring-purple-300' : '' ?>
                        <?= $stepNum > $currentStep ? 'bg-white/10 text-gray-400' : '' ?>">
                        <?php if ($stepNum < $currentStep) { ?>
                            ✓
                        <?php } else { ?>
                            <?= $stepNum ?>
                        <?php } ?>
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 text-center hidden sm:block">
                        <?= __('wizard.steps.'.$stepId) ?>
                    </span>
                </div>
            <?php } ?>
        </div>
    </div>
    
    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 p-8">
            <!-- Step Content will be loaded here -->
            <div x-show="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-purple-500 border-t-transparent"></div>
                <p class="mt-4 text-gray-300">Cargando...</p>
            </div>
            
            <div x-show="!loading" x-transition>
                <!-- Dynamic step content -->
                <template x-if="currentStep === 1">
                    <?php include INSTALLER_ROOT.'/templates/steps/welcome.php'; ?>
                </template>
                
                <!-- Placeholder for other steps -->
                <template x-if="currentStep > 1 && currentStep < 9">
                    <div class="text-center py-8">
                        <h2 class="text-2xl font-bold text-white mb-4" x-text="steps[currentStep - 1]?.name"></h2>
                        <p class="text-gray-400">Este paso será implementado próximamente.</p>
                    </div>
                </template>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-6">
            <button @click="previousStep()" 
                    x-show="currentStep > 1"
                    class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors flex items-center gap-2">
                ← <?= __('buttons.previous') ?>
            </button>
            <div x-show="currentStep === 1"></div>
            
            <button @click="nextStep()" 
                    x-show="currentStep < totalSteps"
                    :disabled="!canProceed"
                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 
                           disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition-colors flex items-center gap-2">
                <?= __('buttons.next') ?> →
            </button>
            
            <button @click="finish()" 
                    x-show="currentStep === totalSteps"
                    class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 
                           text-white rounded-lg transition-colors flex items-center gap-2">
                <?= __('buttons.finish') ?> ✓
            </button>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="max-w-4xl mx-auto px-4 py-8 text-center text-gray-500 text-sm">
        Larafactu © <?= date('Y') ?> - Instalador v<?= INSTALLER_VERSION ?>
    </footer>
    
    <!-- Alpine.js App -->
    <script>
        function wizardApp() {
            return {
                currentStep: <?= $currentStep ?>,
                totalSteps: 9,
                loading: false,
                canProceed: false,
                remainingMinutes: <?= $remainingMinutes ?? 60 ?>,
                steps: [],
                formData: {},
                errors: {},
                
                async init() {
                    await this.loadStatus();
                    
                    // Update remaining time every minute
                    setInterval(() => {
                        if (this.remainingMinutes > 0) {
                            this.remainingMinutes--;
                        }
                    }, 60000);
                },
                
                async loadStatus() {
                    try {
                        const response = await fetch('api.php?action=status');
                        const data = await response.json();
                        
                        this.currentStep = data.currentStep;
                        this.steps = data.steps;
                        this.remainingMinutes = data.remainingMinutes;
                    } catch (error) {
                        console.error('Error loading status:', error);
                    }
                },
                
                async nextStep() {
                    if (!this.canProceed) return;
                    
                    this.loading = true;
                    
                    try {
                        const response = await fetch('api.php?action=execute', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                step: this.currentStep,
                                data: this.formData
                            })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            this.currentStep = result.nextStep;
                            this.errors = {};
                            this.formData = {};
                        } else {
                            this.errors = result.errors || {};
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    } finally {
                        this.loading = false;
                    }
                },
                
                async previousStep() {
                    if (this.currentStep <= 1) return;
                    
                    try {
                        const response = await fetch('api.php?action=previous', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' }
                        });
                        
                        const result = await response.json();
                        this.currentStep = result.currentStep;
                    } catch (error) {
                        console.error('Error:', error);
                    }
                },
                
                async finish() {
                    await this.nextStep();
                    // Redirect to Laravel app
                    window.location.href = '../';
                },
                
                updateFormData(field, value) {
                    this.formData[field] = value;
                    this.validateField(field);
                },
                
                validateField(field) {
                    // Basic client-side validation
                    // More thorough validation happens server-side
                    this.canProceed = Object.keys(this.formData).length > 0;
                }
            };
        }
    </script>
</body>
</html>

