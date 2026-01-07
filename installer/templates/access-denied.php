<?php
/**
 * Access denied template
 *
 * @var string $locale
 */
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - Larafactu Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800 rounded-2xl shadow-2xl p-8 text-center border border-slate-700">
        <div class="w-16 h-16 mx-auto bg-red-600 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-white mb-4">
            <?= $locale === 'es' ? 'Acceso Denegado' : 'Access Denied' ?>
        </h1>
        
        <p class="text-slate-400 mb-6">
            <?= $locale === 'es'
                ? 'Demasiados intentos fallidos. El acceso ha sido bloqueado temporalmente.'
                : 'Too many failed attempts. Access has been temporarily blocked.' ?>
        </p>
        
        <div class="bg-amber-900/30 border border-amber-500/50 rounded-lg p-4 text-left text-sm">
            <p class="text-amber-200">
                <?= $locale === 'es'
                    ? 'Espere 15 minutos o elimine el archivo:'
                    : 'Wait 15 minutes or delete the file:' ?>
            </p>
            <code class="block mt-2 bg-slate-700 px-2 py-1 rounded text-amber-300 break-all">
                installer/storage/failed_attempts.log
            </code>
        </div>
    </div>
</body>
</html>
