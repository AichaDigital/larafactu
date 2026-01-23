<?php

declare(strict_types=1);

/**
 * API Endpoint for Installer AJAX requests
 */

require_once dirname(__DIR__).'/src/bootstrap.php';

use Installer\Security\AccessControl;
use Installer\Session\InstallState;
use Installer\Steps\StepRegistry;

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($input === null) {
    jsonResponse(['error' => 'Invalid JSON'], 400);
}

// Check access
$accessControl = new AccessControl;
$access = $accessControl->checkAccess($input['token'] ?? null);

if (! $access->isGranted()) {
    jsonResponse(['error' => 'Access denied', 'reason' => $access->getReason()], 403);
}

// Get action
$action = $input['action'] ?? '';

// Initialize state and registry
$state = new InstallState;
$registry = new StepRegistry($state);

// Handle actions
switch ($action) {
    case 'get_step':
        handleGetStep($input, $registry, $state);
        break;

    case 'execute_step':
        handleExecuteStep($input, $registry, $state);
        break;

    case 'finalize':
        handleFinalize($registry, $state);
        break;

    default:
        jsonResponse(['error' => 'Unknown action'], 400);
}

/**
 * Get step content
 */
function handleGetStep(array $input, StepRegistry $registry, InstallState $state): never
{
    $stepId = $input['step'] ?? $state->getCurrentStep();
    $step = $registry->get($stepId);

    if ($step === null) {
        jsonResponse(['error' => 'Step not found'], 404);
    }

    $viewData = $step->getViewData();
    $content = view($step->getView(), $viewData);

    jsonResponse([
        'success' => true,
        'content' => $content,
        'defaults' => ! empty($viewData['defaults']) ? $viewData['defaults'] : new \stdClass,
        'step' => [
            'id' => $step->getId(),
            'title' => $step->getTitle(),
        ],
    ]);
}

/**
 * Execute step validation and action
 */
function handleExecuteStep(array $input, StepRegistry $registry, InstallState $state): never
{
    $stepId = $input['step'] ?? '';
    $data = $input['data'] ?? [];

    $step = $registry->get($stepId);

    if ($step === null) {
        jsonResponse(['error' => 'Step not found'], 404);
    }

    // Validate
    $validation = $step->validate($data);

    if (! $validation->isValid()) {
        jsonResponse([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validation->getErrors(),
        ], 422);
    }

    // Execute
    $result = $step->execute($data);

    if ($result->isSuccess()) {
        // Move to next step
        $nextStep = $registry->getNext($stepId);
        if ($nextStep) {
            $state->setCurrentStep($nextStep->getId());
        }

        jsonResponse([
            'success' => true,
            'message' => $result->getMessage(),
            'data' => $result->getData(),
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => $result->getMessage(),
            'error' => $result->get('error'),
        ], 422);
    }
}

/**
 * Handle finalization
 */
function handleFinalize(StepRegistry $registry, InstallState $state): never
{
    $step = $registry->get('finalize');

    if ($step === null) {
        jsonResponse(['error' => 'Finalize step not found'], 404);
    }

    // Validate all steps completed
    $validation = $step->validate([]);

    if (! $validation->isValid()) {
        jsonResponse([
            'success' => false,
            'message' => 'Not all steps completed',
            'errors' => $validation->getErrors(),
        ], 422);
    }

    // Execute finalization
    $result = $step->execute([]);

    if ($result->isSuccess()) {
        // Get app URL for redirect
        $protocol = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['SCRIPT_NAME']);
        $path = str_replace('/installer/public', '', $path);
        $path = str_replace('/installer', '', $path);
        $appUrl = $protocol.'://'.$host.$path;

        jsonResponse([
            'success' => true,
            'message' => __('finalize.title'),
            'redirect' => $appUrl,
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => $result->getMessage(),
            'error' => $result->get('error'),
        ], 422);
    }
}
