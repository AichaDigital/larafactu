<?php

declare(strict_types=1);

/**
 * Larafactu Installation Wizard Entry Point
 */

require_once dirname(__DIR__).'/src/bootstrap.php';

use Installer\Security\AccessControl;
use Installer\Session\InstallState;
use Installer\Steps\StepRegistry;

// Check if already installed
$doneFile = INSTALLER_ROOT.'/.done';
if (file_exists($doneFile)) {
    $doneData = json_decode(file_get_contents($doneFile), true);
    $completedAt = $doneData['completed_at'] ?? 0;
    $graceHours = 24; // Grace period to delete installer

    if (time() - $completedAt > ($graceHours * 3600)) {
        // Grace period expired
        http_response_code(503);
        echo view('installer-blocked');
        exit;
    }

    // Show warning
    http_response_code(200);
    echo view('installer-complete', [
        'appUrl' => getAppUrl(),
        'completedAt' => date('Y-m-d H:i:s', $completedAt),
        'graceEnds' => date('Y-m-d H:i:s', $completedAt + ($graceHours * 3600)),
    ]);
    exit;
}

// Access control
$accessControl = new AccessControl;

// Handle token submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['access_token'])) {
    $access = $accessControl->checkAccess($_POST['access_token']);

    if ($access->isGranted()) {
        $_SESSION['installer_token'] = $_POST['access_token'];
        header('Location: '.$_SERVER['REQUEST_URI']);
        exit;
    }
}

// Check access
$sessionToken = $_SESSION['installer_token'] ?? null;
$access = $accessControl->checkAccess($sessionToken);

// Handle different access states
switch ($access->getReason()) {
    case 'expired':
        echo view('session-expired', ['locale' => getLocale()]);
        exit;

    case 'invalid':
    case 'missing':
        $accessControl->ensureTokenExists();
        echo view('token-form', [
            'locale' => getLocale(),
            'error' => $access->getReason() === 'invalid' ? __('errors.invalid_token') : null,
        ]);
        exit;

    case 'blocked':
        echo view('access-denied', ['locale' => getLocale()]);
        exit;
}

// Access granted - show wizard
$state = new InstallState;
$registry = new StepRegistry($state);

$currentStepId = $state->getCurrentStep();
$currentStep = $registry->get($currentStepId);

// Render wizard
echo view('wizard', [
    'currentStep' => [
        'id' => $currentStepId,
        'title' => $currentStep?->getTitle() ?? '',
    ],
    'steps' => $registry->getAll(),
    'completedSteps' => $state->getCompletedSteps(),
    'token' => $sessionToken,
    'locale' => getLocale(),
]);

/**
 * Get application URL
 */
function getAppUrl(): string
{
    $protocol = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    $path = str_replace('/installer/public', '', $path);
    $path = str_replace('/installer', '', $path);

    return $protocol.'://'.$host.$path;
}
