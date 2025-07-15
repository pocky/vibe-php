<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Exception;

final class ValidationException extends \InvalidArgumentException
{
    private function __construct(
        string $message,
        private readonly string $translationKey,
        private readonly array $translationParameters = [],
        private readonly string|null $translationDomain = 'messages',
    ) {
        parent::__construct($message);
    }

    public static function withTranslationKey(
        string $translationKey,
        array $translationParameters = [],
        string|null $translationDomain = 'messages',
        string|null $fallbackMessage = null
    ): self {
        $message = $fallbackMessage ?? "Validation failed for key: {$translationKey}";

        return new self(
            $message,
            $translationKey,
            $translationParameters,
            $translationDomain
        );
    }

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }

    public function getTranslationParameters(): array
    {
        return $this->translationParameters;
    }

    public function getTranslationDomain(): string|null
    {
        return $this->translationDomain;
    }
}
