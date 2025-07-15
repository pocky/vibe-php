<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\SubmitForReview\Exception;

final class InvalidSubmissionException extends \RuntimeException
{
    public static function alreadyPendingReview(): self
    {
        return new self('Article is already pending review');
    }

    public static function alreadyApproved(): self
    {
        return new self('Article is already approved');
    }

    public static function cannotSubmitPublished(): self
    {
        return new self('Cannot submit published article for review');
    }

    public static function cannotSubmitArchived(): self
    {
        return new self('Cannot submit archived article for review');
    }
}
