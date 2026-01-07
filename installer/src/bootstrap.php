<?php

declare(strict_types=1);

/**
 * Installer Bootstrap
 *
 * Initializes the wizard environment without Laravel.
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Define paths
define('INSTALLER_ROOT', dirname(__DIR__));
define('INSTALLER_VERSION', '1.0.0');
define('LARAFACTU_ROOT', dirname(dirname(__DIR__)));
define('STORAGE_PATH', INSTALLER_ROOT.'/storage');
define('LANG_COOKIE_NAME', 'installer_lang');

// Ensure storage directory exists
if (! is_dir(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0755, true);
}

// PSR-4 style autoloader
spl_autoload_register(function (string $class): void {
    $prefix = 'Installer\\';
    $baseDir = INSTALLER_ROOT.'/src/';

    // Check if class uses the prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get relative class name
    $relativeClass = substr($class, $len);

    // Replace namespace separators with directory separators
    $file = $baseDir.str_replace('\\', '/', $relativeClass).'.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Include helpers
require_once INSTALLER_ROOT.'/src/helpers.php';

// Initialize translator
$translator = new \Installer\I18n\Translator;

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
