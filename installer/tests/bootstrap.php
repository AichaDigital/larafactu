<?php

/**
 * PHPUnit Bootstrap for Installer Tests
 */

declare(strict_types=1);

// Define paths for testing
define('INSTALLER_ROOT', dirname(__DIR__));
define('INSTALLER_VERSION', '1.0.0-test');
define('LARAFACTU_ROOT', dirname(INSTALLER_ROOT));

// Load autoloader
require_once INSTALLER_ROOT.'/src/bootstrap.php';

// Create test storage directory if needed
$testStorage = INSTALLER_ROOT.'/storage';
if (! is_dir($testStorage)) {
    mkdir($testStorage, 0755, true);
}
