<?php

declare(strict_types=1);

namespace Installer\Validators;

/**
 * Validates that required directories are writable.
 */
class WritablePathsValidator implements ValidatorInterface
{
    /**
     * Paths that must be writable (relative to LARAFACTU_ROOT)
     */
    private const REQUIRED_PATHS = [
        'storage' => 'Application storage',
        'storage/app' => 'File storage',
        'storage/framework' => 'Framework cache',
        'storage/framework/cache' => 'Cache storage',
        'storage/framework/sessions' => 'Session storage',
        'storage/framework/views' => 'Compiled views',
        'storage/logs' => 'Log files',
        'bootstrap/cache' => 'Bootstrap cache',
    ];

    /**
     * Files that must be writable
     */
    private const REQUIRED_FILES = [
        '.env' => 'Environment configuration',
    ];

    public function check(): ValidatorResult
    {
        $errors = [];
        $passed = [];

        // Check directories
        foreach (self::REQUIRED_PATHS as $path => $description) {
            $fullPath = LARAFACTU_ROOT.'/'.$path;
            $result = $this->checkPath($fullPath, $path, $description);

            if ($result['status'] === 'error') {
                $errors[$path] = $result;
            } else {
                $passed[$path] = $result;
            }
        }

        // Check files
        foreach (self::REQUIRED_FILES as $file => $description) {
            $fullPath = LARAFACTU_ROOT.'/'.$file;
            $result = $this->checkFile($fullPath, $file, $description);

            if ($result['status'] === 'error') {
                $errors[$file] = $result;
            } else {
                $passed[$file] = $result;
            }
        }

        // Determine overall result
        if (! empty($errors)) {
            $errorPaths = implode(', ', array_keys($errors));

            return ValidatorResult::error(
                __('requirements.path_not_writable', ['path' => $errorPaths]),
                [
                    'passed' => $passed,
                    'errors' => $errors,
                ]
            );
        }

        return ValidatorResult::ok(
            count($passed).' directorios/archivos verificados',
            [
                'passed' => $passed,
                'errors' => $errors,
            ]
        );
    }

    public function getName(): string
    {
        return __('requirements.writable_paths');
    }

    public function getDescription(): string
    {
        return 'Directorios y archivos que deben ser escribibles';
    }

    /**
     * Check if a directory is writable, create if it doesn't exist
     */
    private function checkPath(string $fullPath, string $relativePath, string $description): array
    {
        // Try to create directory if it doesn't exist
        if (! is_dir($fullPath)) {
            $created = @mkdir($fullPath, 0755, true);

            if (! $created) {
                return [
                    'path' => $relativePath,
                    'description' => $description,
                    'status' => 'error',
                    'message' => 'No se pudo crear el directorio',
                ];
            }
        }

        // Check if writable
        if (! is_writable($fullPath)) {
            return [
                'path' => $relativePath,
                'description' => $description,
                'status' => 'error',
                'message' => 'El directorio no es escribible',
            ];
        }

        return [
            'path' => $relativePath,
            'description' => $description,
            'status' => 'ok',
            'message' => 'OK',
        ];
    }

    /**
     * Check if a file is writable or can be created
     */
    private function checkFile(string $fullPath, string $relativePath, string $description): array
    {
        // If file exists, check if writable
        if (file_exists($fullPath)) {
            if (! is_writable($fullPath)) {
                return [
                    'path' => $relativePath,
                    'description' => $description,
                    'status' => 'error',
                    'message' => 'El archivo no es escribible',
                ];
            }

            return [
                'path' => $relativePath,
                'description' => $description,
                'status' => 'ok',
                'message' => 'Existente y escribible',
            ];
        }

        // File doesn't exist, check if we can create it
        $dir = dirname($fullPath);
        if (! is_writable($dir)) {
            return [
                'path' => $relativePath,
                'description' => $description,
                'status' => 'error',
                'message' => 'No se puede crear el archivo (directorio no escribible)',
            ];
        }

        // Try to create an empty file (will be written later)
        // Copy from .env.example if it exists
        $exampleFile = $fullPath.'.example';
        if (file_exists($exampleFile)) {
            $copied = @copy($exampleFile, $fullPath);
            if ($copied) {
                return [
                    'path' => $relativePath,
                    'description' => $description,
                    'status' => 'ok',
                    'message' => 'Creado desde .example',
                ];
            }
        }

        return [
            'path' => $relativePath,
            'description' => $description,
            'status' => 'ok',
            'message' => 'Se puede crear',
        ];
    }

    /**
     * Get list of required paths
     */
    public static function getRequiredPaths(): array
    {
        return self::REQUIRED_PATHS;
    }
}
