<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\DeleteArticle;

use App\BlogContext\Application\Operation\Command\DeleteArticle\Command;
use App\BlogContext\Application\Operation\Command\DeleteArticle\Handler;
use App\BlogContext\Domain\DeleteArticle\DeleterInterface;
use App\BlogContext\Domain\DeleteArticle\Event\ArticleDeleted;
use App\BlogContext\Domain\DeleteArticle\Model\Article as DeleteArticle;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private DeleterInterface $deleter;
    private EventBusInterface $eventBus;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->deleter = $this->createMock(DeleterInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);

        $this->handler = new Handler(
            $this->deleter,
            $this->eventBus
        );
    }

    public function testHandleDeleteArticleCommand(): void
    {
        // Given
        $command = new Command(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            deletedBy: 'user-123'
        );

        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $deleteData = DeleteArticle::create(
            id: $articleId,
            slug: new \App\BlogContext\Domain\Shared\ValueObject\Slug('test-article'),
            deletedBy: 'user-123',
        );

        $event = new ArticleDeleted(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            slug: 'test-article',
            deletedBy: 'user-123',
            deletedAt: $deleteData->deletedAt,
        );

        $deleteData = $deleteData->withEvents([$event]);

        $this->deleter->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(fn ($id) => $id instanceof ArticleId && '550e8400-e29b-41d4-a716-446655440000' === $id->getValue()),
                'user-123'
            )
            ->willReturn($deleteData);

        // EventBus should be called with ArticleDeleted
        $this->eventBus->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ArticleDeleted::class));

        // When
        ($this->handler)($command);
    }
}
