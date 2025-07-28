<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Command\Article\DeleteArticle;

use App\Blog\Application\Operation\Command\Article\DeleteArticle\Command;
use App\Blog\Application\Operation\Command\Article\DeleteArticle\Handler;
use App\Blog\Domain\Article\ArticleDeleter;
use App\Blog\Domain\Article\Shared\Event\ArticleDeleted;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $deleter;

    private \PHPUnit\Framework\MockObject\MockObject $eventBus;

    private Handler $handler;

    protected function setUp(): void
    {
        $this->deleter = $this->createMock(ArticleDeleter::class);
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

        new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        // Mock the deleter to return void (no return value needed)

        $this->deleter->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(fn ($id): bool => $id instanceof ArticleId && '550e8400-e29b-41d4-a716-446655440000' === $id->getValue()),
                'user-123'
            )
        ;

        // EventBus should be called with ArticleDeleted
        $this->eventBus->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ArticleDeleted::class));

        // When
        ($this->handler)($command);
    }
}
