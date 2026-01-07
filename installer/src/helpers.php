<?php

declare(strict_types=1);

/**
 * Global helper functions for the installer
 */
if (! function_exists('__')) {
    /**
     * Translate a string
     */
    function __(string $key, array $replace = []): string
    {
        global $translator;

        if ($translator === null) {
            $translator = new \Installer\I18n\Translator;
        }

        return $translator->translate($key, $replace);
    }
}

if (! function_exists('view')) {
    /**
     * Render a template
     */
    function view(string $template, array $data = []): string
    {
        $path = INSTALLER_ROOT.'/templates/'.$template.'.php';

        if (! file_exists($path)) {
            return "Template not found: {$template}";
        }

        extract($data);

        ob_start();
        include $path;

        return ob_get_clean();
    }
}

if (! function_exists('e')) {
    /**
     * Escape HTML entities
     */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8', false);
    }
}

if (! function_exists('jsonResponse')) {
    /**
     * Send JSON response and exit
     */
    function jsonResponse(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (! function_exists('getLocale')) {
    /**
     * Get current locale
     */
    function getLocale(): string
    {
        global $translator;

        if ($translator !== null) {
            return $translator->getLocale();
        }

        return $_COOKIE[LANG_COOKIE_NAME] ?? 'es';
    }
}

if (! function_exists('csrf_token')) {
    /**
     * Get CSRF token from session
     */
    function csrf_token(): string
    {
        if (! isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}

if (! function_exists('verify_csrf')) {
    /**
     * Verify CSRF token
     */
    function verify_csrf(?string $token): bool
    {
        if ($token === null) {
            return false;
        }

        return hash_equals($_SESSION['_csrf_token'] ?? '', $token);
    }
}
