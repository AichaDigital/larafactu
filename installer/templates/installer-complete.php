<?php
/**
 * Installer already complete template
 *
 * @var string $appUrl
 * @var string $completedAt
 * @var string $graceEnds
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación Completada - Larafactu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-slate-800 rounded-2xl shadow-2xl p-8 border border-slate-700">
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto bg-emerald-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-white mb-2">Instalación Completada</h1>
            <p class="text-slate-400">Larafactu ya está instalado y funcionando</p>
        </div>
        
        <div class="space-y-4">
            <div class="bg-emerald-900/30 border border-emerald-500/50 rounded-lg p-4 text-center">
                <p class="text-emerald-300 mb-2">Su aplicación está disponible en:</p>
                <a href="<?= htmlspecialchars($appUrl) ?>" class="text-emerald-400 hover:text-emerald-300 font-mono text-lg">
                    <?= htmlspecialchars($appUrl) ?>
                </a>
            </div>
            
            <div class="bg-amber-900/30 border border-amber-500/50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-amber-200 text-sm">
                        <strong class="block mb-1">Acción requerida: Elimine el instalador</strong>
                        <p class="text-amber-200/80">
                            Por seguridad, debe eliminar el directorio <code class="bg-slate-700 px-1 rounded">installer/</code> 
                            de su instalación.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-slate-700/50 rounded-lg p-4 text-sm text-slate-400">
                <div class="flex justify-between mb-2">
                    <span>Instalado:</span>
                    <span class="text-white"><?= htmlspecialchars($completedAt) ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Bloqueo automático:</span>
                    <span class="text-amber-400"><?= htmlspecialchars($graceEnds) ?></span>
                </div>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="<?= htmlspecialchars($appUrl) ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 hover:bg-brand-500 text-white font-medium rounded-lg transition">
                Ir a Larafactu
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>
</body>
</html>

