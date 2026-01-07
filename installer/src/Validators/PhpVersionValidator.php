<?php

declare(strict_types=1);

namespace Installer\Validators;

/**
 * Validates PHP version requirement.
 */
class PhpVersionValidator implements ValidatorInterface
{
    private const REQUIRED_VERSION = '8.4.0';

    private const RECOMMENDED_VERSION = '8.4.0';

    public function check(): ValidatorResult
    {
        $currentVersion = PHP_VERSION;

        if (version_compare($currentVersion, self::REQUIRED_VERSION, '<')) {
            return ValidatorResult::error(
                __('requirements.php_required', ['version' => self::REQUIRED_VERSION]),
                [
                    'current' => $currentVersion,
                    'required' => self::REQUIRED_VERSION,
                ]
            );
        }

        if (version_compare($currentVersion, self::RECOMMENDED_VERSION, '<')) {
            return ValidatorResult::warning(
                "PHP {$currentVersion} detectado. Se recomienda PHP ".self::RECOMMENDED_VERSION,
                [
                    'current' => $currentVersion,
                    'recommended' => self::RECOMMENDED_VERSION,
                ]
            );
        }

        return ValidatorResult::ok(
            "PHP {$currentVersion}",
            ['version' => $currentVersion]
        );
    }

    public function getName(): string
    {
        return __('requirements.php_version');
    }

    public function getDescription(): string
    {
        return __('requirements.php_required', ['version' => self::REQUIRED_VERSION]);
    }
}
