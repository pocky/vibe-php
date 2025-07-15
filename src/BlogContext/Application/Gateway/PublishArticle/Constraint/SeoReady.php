<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\PublishArticle\Constraint;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class SeoReady extends Constraint
{
    public string $titleTooShort = 'validation.seo.title.too_short';
    public string $contentTooShort = 'validation.seo.content.too_short';
    public string $missingMetaDescription = 'validation.seo.meta_description.missing';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
