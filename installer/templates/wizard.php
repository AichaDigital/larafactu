<?php
/**
 * Main wizard template with Alpine.js
 *
 * @var array $currentStep
 * @var array $steps
 * @var string $token
 */
$currentStepId = $currentStep['id'] ?? 'welcome';
?>
<!DOCTYPE html>
<html lang="<?= $locale ?? 'es' ?>" x-data="wizardApp()" x-init="init()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Larafactu - <?= __('welcome.title') ?></title>
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand': {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        .step-indicator {
            transition: all 0.3s ease;
        }
        
        .step-indicator.completed {
            background-color: #10b981;
        }
        
        .step-indicator.current {
            background-color: #0ea5e9;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .fade-enter {
            opacity: 0;
            transform: translateX(20px);
        }
        
        .fade-enter-active {
            opacity: 1;
            transform: translateX(0);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        
        <!-- Header -->
        <header class="text-center mb-8">
            <div class="inline-flex items-center gap-3 mb-4">
                <svg class="w-12 h-12 text-brand-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h1 class="text-3xl font-bold text-white">Larafactu</h1>
            </div>
            <p class="text-slate-400"><?= __('welcome.description') ?></p>
        </header>
        
        <!-- Progress Steps -->
        <nav class="mb-8">
            <ol class="flex items-center justify-center flex-wrap gap-2">
                <template x-for="(step, index) in steps" :key="step.id">
                    <li class="flex items-center">
                        <div class="flex items-center gap-2">
                            <span 
                                class="step-indicator w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                                :class="{
                                    'completed bg-emerald-500 text-white': isCompleted(step.id),
                                    'current bg-brand-500 text-white': !isCompleted(step.id) && step.id === currentStep,
                                    'bg-slate-700 text-slate-400': !isCompleted(step.id) && step.id !== currentStep
                                }"
                                x-text="index + 1"
                            ></span>
                            <span 
                                class="text-sm hidden sm:inline"
                                :class="step.id === currentStep ? 'text-white font-medium' : 'text-slate-500'"
                                x-text="step.title"
                            ></span>
                        </div>
                        <span 
                            x-show="index < steps.length - 1" 
                            class="mx-2 text-slate-600"
                        >→</span>
                    </li>
                </template>
            </ol>
        </nav>
        
        <!-- Main Content -->
        <main 
            class="bg-slate-800 rounded-2xl shadow-2xl p-8 border border-slate-700"
            x-cloak
        >
            <!-- Loading Overlay -->
            <div 
                x-show="loading" 
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
            >
                <div class="bg-slate-800 rounded-lg p-6 flex items-center gap-4">
                    <svg class="animate-spin h-8 w-8 text-brand-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-white" x-text="loadingMessage || '<?= __('common.loading') ?>'"></span>
                </div>
            </div>
            
            <!-- Error Alert -->
            <div 
                x-show="error" 
                x-transition 
                class="mb-6 bg-red-900/50 border border-red-500 rounded-lg p-4 text-red-200"
            >
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-medium">Error</p>
                        <p class="text-sm" x-text="error"></p>
                    </div>
                    <button @click="error = null" class="ml-auto">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Success Alert -->
            <div 
                x-show="success" 
                x-transition 
                class="mb-6 bg-emerald-900/50 border border-emerald-500 rounded-lg p-4 text-emerald-200"
            >
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p x-text="success"></p>
                </div>
            </div>
            
            <!-- Step Content -->
            <div x-html="stepContent"></div>
            
            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8 pt-6 border-t border-slate-700">
                <button 
                    @click="prevStep()" 
                    x-show="currentStepIndex > 0"
                    class="px-6 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-medium transition flex items-center gap-2"
                    :disabled="loading"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <?= __('common.back') ?>
                </button>
                
                <div x-show="currentStepIndex === 0"></div>
                
                <button 
                    @click="nextStep()" 
                    x-show="currentStepIndex < steps.length - 1"
                    class="px-6 py-3 rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium transition flex items-center gap-2"
                    :disabled="loading || !canProceed"
                >
                    <?= __('common.next') ?>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                
                <button 
                    @click="finishInstallation()" 
                    x-show="currentStepIndex === steps.length - 1"
                    class="px-6 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium transition flex items-center gap-2"
                    :disabled="loading"
                >
                    <?= __('finalize.finish_button') ?>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="text-center mt-8 text-slate-500 text-sm">
            <p>Larafactu Installer v<?= INSTALLER_VERSION ?> | <?= __('common.language') ?>: 
                <button @click="changeLanguage('es')" class="hover:text-white" :class="locale === 'es' ? 'text-brand-400' : ''">ES</button> |
                <button @click="changeLanguage('en')" class="hover:text-white" :class="locale === 'en' ? 'text-brand-400' : ''">EN</button>
            </p>
        </footer>
    </div>
    
    <script>
        function wizardApp() {
            return {
                // State
                steps: <?= json_encode(array_values($steps)) ?>,
                currentStep: '<?= $currentStepId ?>',
                currentStepIndex: 0,
                completedSteps: <?= json_encode($completedSteps ?? []) ?>,
                formData: {},
                stepContent: '',
                
                // UI State
                loading: false,
                loadingMessage: '',
                error: null,
                success: null,
                canProceed: true,
                locale: '<?= $locale ?? 'es' ?>',
                token: '<?= $token ?>',
                
                // Initialize
                init() {
                    this.currentStepIndex = this.steps.findIndex(s => s.id === this.currentStep);
                    this.loadStepContent();
                },
                
                // Check if step is completed
                isCompleted(stepId) {
                    return this.completedSteps.includes(stepId);
                },
                
                // Load step content via AJAX
                async loadStepContent() {
                    this.loading = true;
                    this.error = null;
                    
                    try {
                        const response = await fetch('api.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'get_step',
                                step: this.currentStep,
                                token: this.token
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.stepContent = data.content;
                            this.formData = data.defaults || {};
                        } else {
                            this.error = data.error || 'Error loading step';
                        }
                    } catch (e) {
                        this.error = 'Network error: ' + e.message;
                    } finally {
                        this.loading = false;
                    }
                },
                
                // Go to next step
                async nextStep() {
                    this.loading = true;
                    this.loadingMessage = '<?= __('common.validating') ?>';
                    this.error = null;
                    this.success = null;

                    // Get form data from current step
                    const form = document.querySelector('#step-form');
                    if (form) {
                        const formData = new FormData(form);
                        formData.forEach((value, key) => {
                            this.formData[key] = value;
                        });
                    }

                    try {
                        const response = await fetch('api.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'execute_step',
                                step: this.currentStep,
                                data: this.formData,
                                token: this.token
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            // Mark current step as completed
                            if (!this.completedSteps.includes(this.currentStep)) {
                                this.completedSteps.push(this.currentStep);
                            }
                            
                            // Move to next step
                            this.currentStepIndex++;
                            this.currentStep = this.steps[this.currentStepIndex].id;
                            this.success = data.message;
                            this.formData = {};
                            
                            // Load next step content
                            await this.loadStepContent();
                        } else {
                            this.error = data.error || data.message || 'Validation failed';
                            if (data.errors) {
                                const errorList = Object.values(data.errors).join(', ');
                                this.error += ': ' + errorList;
                            }
                        }
                    } catch (e) {
                        this.error = 'Network error: ' + e.message;
                    } finally {
                        this.loading = false;
                        this.loadingMessage = '';
                    }
                },
                
                // Go to previous step
                prevStep() {
                    if (this.currentStepIndex > 0) {
                        this.currentStepIndex--;
                        this.currentStep = this.steps[this.currentStepIndex].id;
                        this.error = null;
                        this.success = null;
                        this.loadStepContent();
                    }
                },
                
                // Finish installation
                async finishInstallation() {
                    this.loading = true;
                    this.loadingMessage = '<?= __('finalize.finishing') ?>';
                    
                    try {
                        const response = await fetch('api.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'finalize',
                                token: this.token
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.completedSteps.push(this.currentStep);
                            this.success = data.message;
                            
                            // Show completion message and redirect
                            setTimeout(() => {
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                }
                            }, 2000);
                        } else {
                            this.error = data.error || 'Finalization failed';
                        }
                    } catch (e) {
                        this.error = 'Network error: ' + e.message;
                    } finally {
                        this.loading = false;
                    }
                },
                
                // Change language
                async changeLanguage(lang) {
                    this.locale = lang;
                    document.cookie = `installer_lang=${lang};path=/;max-age=86400`;
                    window.location.reload();
                },
                
                // Submit form data
                submitForm(event) {
                    event.preventDefault();
                    this.nextStep();
                }
            }
        }
    </script>
</body>
</html>

