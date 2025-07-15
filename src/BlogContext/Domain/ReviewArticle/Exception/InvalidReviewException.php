<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\ReviewArticle\Exception;

use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;

final class InvalidReviewException extends \RuntimeException
{
    public static function invalidStatus(ArticleStatus $status): self
    {
        return new self(sprintf('Cannot review article with status: %s', $status->getValue()));
    }

    public static function alreadyApproved(): self
    {
        return new self('Article has already been approved');
    }

    public static function cannotReviewPublished(): self
    {
        return new self('Cannot review published article');
    }

    public static function cannotReviewArchived(): self
    {
        return new self('Cannot review archived article');
    }
}
