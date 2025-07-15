<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\PublishArticle\Exception;

final class SeoValidationException extends \InvalidArgumentException
{
    public static function titleTooShort(int $minLength, int $actualLength): self
    {
        return new self(
            sprintf(
                'Article title is too short for SEO. Minimum length: %d characters, actual: %d characters',
                $minLength,
                $actualLength
            )
        );
    }

    public static function contentTooShort(int $minLength, int $actualLength): self
    {
        return new self(
            sprintf(
                'Article content is too short for SEO. Minimum length: %d characters, actual: %d characters',
                $minLength,
                $actualLength
            )
        );
    }

    public static function missingMetaDescription(): self
    {
        return new self('Article is missing meta description for SEO');
    }
}
