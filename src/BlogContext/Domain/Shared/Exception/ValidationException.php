<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Exception;

final class ValidationException extends \InvalidArgumentException
{
    public function __construct(
        private readonly string $translationKey,
        private readonly array $parameters = [],
        string $message = '',
        int $code = 0,
        \Throwable|null $previous = null
    ) {
        parent::__construct($message ?: $translationKey, $code, $previous);
    }

    public static function withTranslationKey(string $translationKey, array $parameters = []): self
    {
        return new self($translationKey, $parameters);
    }

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
