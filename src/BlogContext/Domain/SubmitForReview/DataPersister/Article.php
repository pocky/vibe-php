<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\SubmitForReview\DataPersister;

use App\BlogContext\Domain\Shared\Event\EventRecorder;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\BlogContext\Domain\SubmitForReview\Event\ArticleSubmittedForReview;

final class Article
{
    use EventRecorder;

    public function __construct(
        private readonly ArticleId $articleId,
        private readonly Title $title,
        private readonly ArticleStatus $status,
        private readonly \DateTimeImmutable $submittedAt,
        private readonly string|null $authorId = null,
    ) {
    }

    public static function submitForReview(
        ArticleId $articleId,
        Title $title,
        ArticleStatus $status,
        \DateTimeImmutable $submittedAt,
        string|null $authorId = null,
    ): self {
        $article = new self($articleId, $title, $status, $submittedAt, $authorId);

        $article->recordEvent(new ArticleSubmittedForReview(
            articleId: $articleId->getValue(),
            title: $title->getValue(),
            authorId: $authorId ?? '00000000-0000-0000-0000-000000000000', // Default if no author
            submittedAt: $submittedAt,
        ));

        return $article;
    }

    public function getArticleId(): ArticleId
    {
        return $this->articleId;
    }

    public function getTitle(): Title
    {
        return $this->title;
    }

    public function getStatus(): ArticleStatus
    {
        return $this->status;
    }

    public function getSubmittedAt(): \DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function getAuthorId(): string|null
    {
        return $this->authorId;
    }
}
