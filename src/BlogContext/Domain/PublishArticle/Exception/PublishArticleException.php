<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle\Exception;

abstract class PublishArticleException extends \DomainException
{
    protected function __construct(
        string $message,
        \Throwable|null $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
