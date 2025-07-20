<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\PublishArticle;

final readonly class Command
{
    public function __construct(
        public string $articleId,
        public string|null $publishAt = null,
    ) {
        if ('' === $this->articleId) {
            throw new \InvalidArgumentException('Article ID cannot be empty');
        }

        if (null !== $this->publishAt && !$this->isValidDateTimeString($this->publishAt)) {
            throw new \InvalidArgumentException('Invalid publish date format');
        }
    }

    private function isValidDateTimeString(string $dateTime): bool
    {
        try {
            new \DateTimeImmutable($dateTime);

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
