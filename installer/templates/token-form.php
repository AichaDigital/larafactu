<?php
/**
 * Access token form template
 *
 * @var string $locale
 * @var string|null $error
 */
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Larafactu Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800 rounded-2xl shadow-2xl p-8 border border-slate-700">
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto bg-brand-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-white mb-2">Larafactu Installer</h1>
            <p class="text-slate-400"><?= $locale === 'es' ? 'Ingrese el token de acceso para continuar' : 'Enter access token to continue' ?></p>
        </div>
        
        <?php if ($error) { ?>
        <div class="mb-6 bg-red-900/50 border border-red-500 rounded-lg p-4 text-red-200 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php } ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label for="access_token" class="block text-sm font-medium text-slate-300 mb-1">
                    Token de acceso
                </label>
                <input 
                    type="text" 
                    name="access_token" 
                    id="access_token"
                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent font-mono"
                    placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                    autofocus
                    required
                >
            </div>
            
            <button type="submit" class="w-full bg-brand-600 hover:bg-brand-500 text-white font-medium py-3 px-4 rounded-lg transition">
                <?= $locale === 'es' ? 'Acceder' : 'Access' ?>
            </button>
        </form>
        
        <div class="mt-6 bg-blue-900/30 border border-blue-500/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-blue-200 text-sm">
                    <p class="mb-2"><?= $locale === 'es' ? 'El token se encuentra en:' : 'The token is located at:' ?></p>
                    <code class="block bg-slate-700 px-2 py-1 rounded text-xs break-all">installer/storage/.token</code>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-center text-sm text-slate-500">
            <button onclick="toggleLang()" class="hover:text-white">
                <?= $locale === 'es' ? 'English' : 'Español' ?>
            </button>
        </div>
    </div>
    
    <script>
        function toggleLang() {
            const current = '<?= $locale ?>';
            const newLang = current === 'es' ? 'en' : 'es';
            document.cookie = 'installer_lang=' + newLang + ';path=/;max-age=86400';
            location.reload();
        }
    </script>
    
    <style>
        .bg-brand-600 { background-color: #0284c7; }
        .bg-brand-500 { background-color: #0ea5e9; }
        .focus\:ring-brand-500:focus { --tw-ring-color: #0ea5e9; }
    </style>
</body>
</html>
