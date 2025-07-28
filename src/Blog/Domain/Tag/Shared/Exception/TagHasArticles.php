<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag\Shared\Exception;

use App\Blog\Domain\Tag\Shared\Identifier\TagId;

final class TagHasArticles extends \DomainException
{
    public function __construct(TagId $tagId, int $articleCount)
    {
        parent::__construct(sprintf(
            'Cannot delete tag %s because it has %d articles',
            $tagId->getValue(),
            $articleCount
        ));
    }
}
