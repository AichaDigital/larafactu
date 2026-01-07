<?php
/**
 * Session expired template
 *
 * @var string $locale
 */
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión Expirada - Larafactu Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800 rounded-2xl shadow-2xl p-8 text-center border border-slate-700">
        <div class="w-16 h-16 mx-auto bg-amber-600 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-white mb-4">
            <?= $locale === 'es' ? 'Sesión Expirada' : 'Session Expired' ?>
        </h1>
        
        <p class="text-slate-400 mb-6">
            <?= $locale === 'es'
                ? 'Su sesión ha expirado por seguridad. Necesita un nuevo token de acceso.'
                : 'Your session has expired for security. You need a new access token.' ?>
        </p>
        
        <div class="bg-blue-900/30 border border-blue-500/50 rounded-lg p-4 text-left text-sm mb-6">
            <p class="text-blue-200 mb-2">
                <?= $locale === 'es' ? 'Pasos para continuar:' : 'Steps to continue:' ?>
            </p>
            <ol class="text-blue-200/80 list-decimal list-inside space-y-1">
                <li><?= $locale === 'es'
                    ? 'Elimine el archivo <code class="bg-slate-700 px-1 rounded">.token</code>'
                    : 'Delete the file <code class="bg-slate-700 px-1 rounded">.token</code>' ?></li>
                <li><?= $locale === 'es'
                    ? 'Recargue esta página'
                    : 'Reload this page' ?></li>
                <li><?= $locale === 'es'
                    ? 'Un nuevo token será generado'
                    : 'A new token will be generated' ?></li>
            </ol>
        </div>
        
        <a href="?" class="inline-flex items-center gap-2 px-6 py-3 bg-amber-600 hover:bg-amber-500 text-white font-medium rounded-lg transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <?= $locale === 'es' ? 'Recargar' : 'Reload' ?>
        </a>
    </div>
</body>
</html>
