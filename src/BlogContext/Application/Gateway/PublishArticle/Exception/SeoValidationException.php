<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\PublishArticle\Exception;

use Symfony\Contracts\Translation\TranslatorInterface;

final class SeoValidationException extends \InvalidArgumentException
{
    public static function titleTooShort(int $minLength, int $actualLength, TranslatorInterface|null $translator = null): self
    {
        $message = $translator?->trans('validation.seo.title.too_short', [
            'min_length' => $minLength,
            'actual_length' => $actualLength,
        ], 'messages') ?? sprintf(
            'Article title is too short for SEO. Minimum length: %d characters, actual: %d characters',
            $minLength,
            $actualLength
        );

        return new self($message);
    }

    public static function contentTooShort(int $minLength, int $actualLength, TranslatorInterface|null $translator = null): self
    {
        $message = $translator?->trans('validation.seo.content.too_short', [
            'min_length' => $minLength,
            'actual_length' => $actualLength,
        ], 'messages') ?? sprintf(
            'Article content is too short for SEO. Minimum length: %d characters, actual: %d characters',
            $minLength,
            $actualLength
        );

        return new self($message);
    }

    public static function missingMetaDescription(TranslatorInterface|null $translator = null): self
    {
        $message = $translator?->trans('validation.seo.meta_description.missing', [], 'messages')
            ?? 'Article is missing meta description for SEO';

        return new self($message);
    }
}
