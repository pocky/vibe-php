<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\UpdateArticle;

use App\BlogContext\Application\Operation\Command\UpdateArticle\{Command, Handler};
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};
use App\BlogContext\Domain\UpdateArticle\{DataPersister\Article, UpdaterInterface};
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    private UpdaterInterface&\PHPUnit\Framework\MockObject\MockObject $updater;
    private EventBusInterface&\PHPUnit\Framework\MockObject\MockObject $eventBus;
    private Handler $handler;

    #[\Override]
    protected function setUp(): void
    {
        $this->updater = $this->createMock(UpdaterInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);
        $this->handler = new Handler($this->updater, $this->eventBus);
    }

    public function testHandlerExecutesUpdateAndDispatchesEvents(): void
    {
        $commandArticleIdValue = $this->generateArticleId()->getValue();
        $commandArticleId = new ArticleId($commandArticleIdValue);
        $commandTitle = new Title('Updated Title');
        $commandContent = new Content('Updated content with sufficient length for testing.');

        $command = new Command(
            articleId: $commandArticleId,
            title: $commandTitle,
            content: $commandContent,
            slug: new Slug('updated-title'),
            status: ArticleStatus::PUBLISHED
        );

        // Create real article object using value objects
        $resultArticleIdValue = $this->generateArticleId()->getValue();
        $resultArticleId = new ArticleId($resultArticleIdValue);
        $resultTitle = new Title('Updated Title');
        $resultContent = new Content('Updated content with sufficient length for testing.');

        $updatedArticle = new Article(
            id: $resultArticleId,
            title: $resultTitle,
            content: $resultContent,
            slug: new Slug('updated-title'),
            status: ArticleStatus::DRAFT,
            createdAt: new \DateTimeImmutable('2024-01-01'),
            updatedAt: new \DateTimeImmutable(),
            originalTitle: new Title('Original Title'),
            originalContent: new Content('Original content.')
        );

        // Expect updater to be called with correct value objects
        $this->updater->expects(self::once())
            ->method('__invoke')
            ->with(
                self::callback(fn (ArticleId $id) => $commandArticleIdValue === $id->getValue()),
                self::callback(fn (Title $title) => 'Updated Title' === $title->getValue()),
                self::callback(fn (Content $content) => 'Updated content with sufficient length for testing.' === $content->getValue())
            )
            ->willReturn($updatedArticle);

        // Expect event to be dispatched at least once
        $this->eventBus->expects(self::atLeastOnce())
            ->method('__invoke');

        // Execute
        $result = ($this->handler)($command);

        // Verify result is the updated article
        self::assertSame($updatedArticle, $result);
    }

    public function testHandlerIsReadonly(): void
    {
        $reflection = new \ReflectionClass($this->handler);
        self::assertTrue($reflection->isReadOnly(), 'Handler should be readonly');
    }
}
