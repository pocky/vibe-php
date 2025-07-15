<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Shared\Translation;

use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class UiTranslationService
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function translateApiError(string $errorType, array $parameters = []): string
    {
        return $this->translator->trans("error.api.{$errorType}", $parameters, 'messages');
    }

    public function translateValidationError(string $field, string $errorType, array $parameters = []): string
    {
        return $this->translator->trans("validation.{$field}.{$errorType}", $parameters, 'messages');
    }

    public function translateFormError(string $field, string $errorType, array $parameters = []): string
    {
        return $this->translator->trans("app.article.{$field}.{$errorType}", $parameters, 'messages');
    }

    public function translateUiMessage(string $messageKey, array $parameters = []): string
    {
        return $this->translator->trans($messageKey, $parameters, 'messages');
    }

    public function translateArticleError(string $errorType, array $parameters = []): string
    {
        return $this->translator->trans("error.article.{$errorType}", $parameters, 'messages');
    }
}
