#!/usr/bin/env php
<?php

/**
 * Post-Deploy Script for Production
 *
 * Converts local path repositories to VCS (GitHub) repositories
 * for production environments where symlinks don't exist.
 *
 * Usage: php scripts/post-deploy.php
 */
echo "🚀 Larafactu - Post-Deploy Script\n";
echo "==================================\n\n";

$composerFile = __DIR__.'/../composer.json';

if (! file_exists($composerFile)) {
    echo "❌ Error: composer.json not found\n";
    exit(1);
}

echo "📝 Reading composer.json...\n";
$composer = json_decode(file_get_contents($composerFile), true);

if (! $composer) {
    echo "❌ Error: Could not parse composer.json\n";
    exit(1);
}

// Check if we have path repositories (local symlinks)
$hasPathRepos = false;
if (isset($composer['repositories'])) {
    foreach ($composer['repositories'] as $repo) {
        if (isset($repo['type']) && $repo['type'] === 'path') {
            $hasPathRepos = true;
            break;
        }
    }
}

if (! $hasPathRepos) {
    echo "✓ Already using VCS repositories (production mode)\n";
    echo "✓ No changes needed\n\n";
    exit(0);
}

echo "🔄 Converting path repositories to VCS (GitHub)...\n\n";

// Replace path repositories with VCS (GitHub)
$composer['repositories'] = [
    [
        'type' => 'vcs',
        'url' => 'https://github.com/AichaDigital/larabill',
    ],
    [
        'type' => 'vcs',
        'url' => 'https://github.com/AichaDigital/lararoi',
    ],
    [
        'type' => 'vcs',
        'url' => 'https://github.com/AichaDigital/lara-verifactu',
    ],
    [
        'type' => 'vcs',
        'url' => 'https://github.com/AichaDigital/laratickets',
    ],
];

// Write back to file
$json = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents($composerFile, $json);

echo "✅ Updated composer.json for production:\n";
echo "   - aichadigital/larabill → GitHub\n";
echo "   - aichadigital/lararoi → GitHub\n";
echo "   - aichadigital/lara-verifactu → GitHub\n";
echo "   - aichadigital/laratickets → GitHub\n\n";

echo "📦 Next steps:\n";
echo "   1. Copy .env.example to .env and configure your database\n";
echo "   2. Run: composer install --no-dev --optimize-autoloader\n";
echo "   3. Run: php artisan key:generate (if APP_KEY is empty)\n";
echo "   4. Run: php artisan migrate --force\n";
echo "   5. Run: php artisan larabill:install --no-interaction\n";
echo "   6. Run: php artisan migrate --force (for Larabill tables)\n\n";

echo "⚠️  IMPORTANT: Configure .env BEFORE running migrations!\n";
echo "   Required: DB_*, APP_KEY, LARABILL_USER_ID_TYPE=uuid_binary\n\n";

echo "✅ Post-deploy script completed successfully!\n";
