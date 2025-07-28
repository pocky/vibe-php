<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag\Shared\Exception;

use App\Blog\Domain\Tag\Shared\Identifier\TagId;

final class TagNotFound extends \RuntimeException
{
    public static function withId(TagId $tagId): self
    {
        return new self(sprintf('Tag not found: %s', $tagId->getValue()));
    }
}
