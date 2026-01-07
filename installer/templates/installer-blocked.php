<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador Bloqueado - Larafactu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800 rounded-2xl shadow-2xl p-8 text-center border border-slate-700">
        <div class="w-16 h-16 mx-auto bg-red-600 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-white mb-4">Instalador Bloqueado</h1>
        
        <p class="text-slate-400 mb-6">
            La instalación se completó hace más de 24 horas. 
            Por seguridad, el instalador ha sido deshabilitado.
        </p>
        
        <div class="bg-amber-900/30 border border-amber-500/50 rounded-lg p-4 text-left">
            <p class="text-amber-200 text-sm">
                <strong>Para eliminar esta página:</strong>
            </p>
            <ol class="text-amber-200/80 text-sm mt-2 list-decimal list-inside space-y-1">
                <li>Conecte por SSH o FTP</li>
                <li>Elimine el directorio <code class="bg-slate-700 px-1 rounded">installer/</code></li>
                <li>Acceda a su aplicación normalmente</li>
            </ol>
        </div>
        
        <div class="mt-6 text-sm text-slate-500">
            <p>Si necesita reinstalar, elimine el archivo <code class="bg-slate-700 px-1 rounded">.done</code> del directorio installer/</p>
        </div>
    </div>
</body>
</html>

