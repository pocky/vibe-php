<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\PublishArticle;

use App\BlogContext\Application\Operation\Command\PublishArticle\Command;
use App\BlogContext\Application\Operation\Command\PublishArticle\Handler;
use App\BlogContext\Domain\PublishArticle\Event\ArticlePublished;
use App\BlogContext\Domain\PublishArticle\Model\Article as PublishArticle;
use App\BlogContext\Domain\PublishArticle\PublisherInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private PublisherInterface $publisher;
    private EventBusInterface $eventBus;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->publisher = $this->createMock(PublisherInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);

        $this->handler = new Handler(
            $this->publisher,
            $this->eventBus
        );
    }

    public function testHandlePublishArticleCommand(): void
    {
        // Given
        $command = new Command(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            publishAt: null
        );

        $publishedArticle = new PublishArticle(
            id: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            slug: new Slug('test-article'),
            status: ArticleStatus::PUBLISHED,
            timestamps: Timestamps::create()->withUpdatedAt(new \DateTimeImmutable()),
            publishedAt: new \DateTimeImmutable(),
            events: [
                new ArticlePublished(
                    articleId: '550e8400-e29b-41d4-a716-446655440000',
                    slug: 'test-article',
                    publishedAt: new \DateTimeImmutable()
                ),
            ]
        );

        $this->publisher->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(fn ($id) => $id instanceof ArticleId && '550e8400-e29b-41d4-a716-446655440000' === $id->getValue()),
                $this->isNull()
            )
            ->willReturn($publishedArticle);

        $this->eventBus->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ArticlePublished::class));

        // When
        ($this->handler)($command);
    }

    public function testHandlePublishArticleWithScheduledDate(): void
    {
        // Given
        $publishAt = new \DateTimeImmutable('2025-12-31 12:00:00');
        $command = new Command(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            publishAt: $publishAt->format(\DateTimeInterface::ATOM)
        );

        $publishedArticle = new PublishArticle(
            id: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            slug: new Slug('test-article'),
            status: ArticleStatus::PUBLISHED,
            timestamps: Timestamps::create()->withUpdatedAt(new \DateTimeImmutable()),
            publishedAt: $publishAt,
            events: [
                new ArticlePublished(
                    articleId: '550e8400-e29b-41d4-a716-446655440000',
                    slug: 'test-article',
                    publishedAt: $publishAt
                ),
            ]
        );

        $this->publisher->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(fn ($id) => $id instanceof ArticleId && '550e8400-e29b-41d4-a716-446655440000' === $id->getValue()),
                $this->callback(fn ($date) => $date instanceof \DateTimeImmutable && '2025-12-31 12:00:00' === $date->format('Y-m-d H:i:s'))
            )
            ->willReturn($publishedArticle);

        $this->eventBus->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ArticlePublished::class));

        // When
        ($this->handler)($command);
    }
}
