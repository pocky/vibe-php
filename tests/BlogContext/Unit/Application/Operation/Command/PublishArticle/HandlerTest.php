<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\PublishArticle;

use App\BlogContext\Application\Operation\Command\PublishArticle\{Command, Handler};
use App\BlogContext\Domain\PublishArticle\{DataPersister\Article, PublisherInterface};
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    private PublisherInterface&\PHPUnit\Framework\MockObject\MockObject $publisher;
    private EventBusInterface&\PHPUnit\Framework\MockObject\MockObject $eventBus;
    private Handler $handler;

    #[\Override]
    protected function setUp(): void
    {
        $this->publisher = $this->createMock(PublisherInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);
        $this->handler = new Handler($this->publisher, $this->eventBus);
    }

    public function testHandlerExecutesPublishAndDispatchesEvents(): void
    {
        $commandArticleIdValue = $this->generateArticleId()->getValue();
        $command = new Command(
            articleId: new ArticleId($commandArticleIdValue)
        );

        // Create real article object
        $resultArticleIdValue = $this->generateArticleId()->getValue();
        $articleId = new ArticleId($resultArticleIdValue);
        $publishedArticle = new Article(
            id: $articleId,
            title: new Title('Test Article'),
            content: new Content('Test content with sufficient length.'),
            slug: new Slug('test-article'),
            status: ArticleStatus::PUBLISHED,
            createdAt: new \DateTimeImmutable('2024-01-01'),
            publishedAt: new \DateTimeImmutable(),
        );

        // Expect publisher to be called with correct value object
        $this->publisher->expects(self::once())
            ->method('__invoke')
            ->with(
                self::callback(fn (ArticleId $id) => $commandArticleIdValue === $id->getValue())
            )
            ->willReturn($publishedArticle);

        // Expect event to be dispatched at least once
        $this->eventBus->expects(self::atLeastOnce())
            ->method('__invoke');

        // Execute
        $result = ($this->handler)($command);

        // Verify result is the published article
        self::assertSame($publishedArticle, $result);
    }

    public function testHandlerIsReadonly(): void
    {
        $reflection = new \ReflectionClass($this->handler);
        self::assertTrue($reflection->isReadOnly(), 'Handler should be readonly');
    }
}
