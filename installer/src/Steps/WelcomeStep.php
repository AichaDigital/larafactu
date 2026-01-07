<?php

declare(strict_types=1);

namespace Installer\Steps;

/**
 * Step 1: Welcome
 *
 * Displays welcome message and language selection.
 */
class WelcomeStep extends AbstractStep
{
    public function getId(): string
    {
        return 'welcome';
    }

    public function validate(array $data): ValidationResult
    {
        // Language is optional, defaults to detected
        return ValidationResult::valid();
    }

    public function execute(array $data): ExecutionResult
    {
        // Save language preference if provided
        if (! empty($data['language'])) {
            $this->state->set('language', $data['language']);
            $this->translator->setLocale($data['language']);

            // Set cookie for persistence
            setcookie(LANG_COOKIE_NAME, $data['language'], time() + 86400, '/');
        }

        return $this->success(__('welcome.start_button'));
    }

    public function getViewData(): array
    {
        return [
            'languages' => [
                'es' => 'Español',
                'en' => 'English',
            ],
            'currentLanguage' => $this->translator->getLocale(),
        ];
    }
}
