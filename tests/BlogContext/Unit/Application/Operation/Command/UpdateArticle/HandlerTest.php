<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\UpdateArticle;

use App\BlogContext\Application\Operation\Command\UpdateArticle\Command;
use App\BlogContext\Application\Operation\Command\UpdateArticle\Handler;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\BlogContext\Domain\UpdateArticle\Event\ArticleUpdated;
use App\BlogContext\Domain\UpdateArticle\Model\Article as UpdateArticle;
use App\BlogContext\Domain\UpdateArticle\UpdaterInterface;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private UpdaterInterface $updater;
    private EventBusInterface $eventBus;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->updater = $this->createMock(UpdaterInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);

        $this->handler = new Handler(
            $this->updater,
            $this->eventBus
        );
    }

    public function testHandleUpdateArticleCommand(): void
    {
        // Given
        $command = new Command(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Updated Title',
            content: 'Updated content',
            slug: 'updated-slug'
        );

        $updatedArticle = new UpdateArticle(
            id: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Updated Title'),
            content: new Content('Updated content'),
            slug: new Slug('updated-slug'),
            status: ArticleStatus::DRAFT,
            authorId: 'author-123',
            timestamps: Timestamps::create()->withUpdatedAt(new \DateTimeImmutable()),
            publishedAt: null,
            events: [
                new ArticleUpdated(
                    articleId: '550e8400-e29b-41d4-a716-446655440000',
                    title: 'Updated Title',
                    slug: 'updated-slug',
                    updatedAt: new \DateTimeImmutable()
                ),
            ],
            changes: [
                'title' => [
                    'old' => 'Old Title',
                    'new' => 'Updated Title',
                ],
                'content' => [
                    'old' => 'Old content',
                    'new' => 'Updated content',
                ],
                'slug' => [
                    'old' => 'old-slug',
                    'new' => 'updated-slug',
                ],
            ]
        );

        $this->updater->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(fn ($id) => $id instanceof ArticleId && '550e8400-e29b-41d4-a716-446655440000' === $id->getValue()),
                $this->callback(fn ($title) => $title instanceof Title && 'Updated Title' === $title->getValue()),
                $this->callback(fn ($content) => $content instanceof Content && 'Updated content' === $content->getValue()),
                $this->callback(fn ($slug) => $slug instanceof Slug && 'updated-slug' === $slug->getValue())
            )
            ->willReturn($updatedArticle);

        $this->eventBus->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ArticleUpdated::class));

        // When
        ($this->handler)($command);
    }

    public function testHandlePartialUpdateCommand(): void
    {
        // Given - Only updating title
        $command = new Command(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'New Title Only',
            content: null,
            slug: null
        );

        $updatedArticle = new UpdateArticle(
            id: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('New Title Only'),
            content: new Content('Original content'),
            slug: new Slug('original-slug'),
            status: ArticleStatus::DRAFT,
            authorId: 'author-123',
            timestamps: Timestamps::create()->withUpdatedAt(new \DateTimeImmutable()),
            publishedAt: null,
            events: [
                new ArticleUpdated(
                    articleId: '550e8400-e29b-41d4-a716-446655440000',
                    title: 'New Title Only',
                    slug: 'existing-slug',
                    updatedAt: new \DateTimeImmutable()
                ),
            ],
            changes: [
                'title' => [
                    'old' => 'Old Title',
                    'new' => 'New Title Only',
                ],
            ]
        );

        $this->updater->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->isInstanceOf(ArticleId::class),
                $this->callback(fn ($title) => $title instanceof Title && 'New Title Only' === $title->getValue()),
                $this->isNull(),
                $this->isNull()
            )
            ->willReturn($updatedArticle);

        $this->eventBus->expects($this->once())
            ->method('__invoke');

        // When
        ($this->handler)($command);
    }
}
