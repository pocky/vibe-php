<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Query\GetArticle;

use App\BlogContext\Application\Operation\Query\GetArticle\Handler;
use App\BlogContext\Application\Operation\Query\GetArticle\Query;
use App\BlogContext\Domain\Shared\Repository\ArticleData;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    private ArticleRepositoryInterface&MockObject $repository;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleRepositoryInterface::class);
        $this->handler = new Handler($this->repository);
    }

    public function testHandleWithExistingArticle(): void
    {
        $articleId = $this->generateArticleId()->getValue();
        $query = new Query($articleId);

        $articleData = new ArticleData(
            id: new ArticleId($articleId),
            title: new Title('Test Article'),
            content: new Content('Test content'),
            slug: new Slug('test-article'),
            status: ArticleStatus::DRAFT,
            createdAt: new \DateTimeImmutable('2024-01-01T12:00:00+00:00'),
            publishedAt: null,
            updatedAt: new \DateTimeImmutable('2024-01-01T12:00:00+00:00')
        );

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(fn (ArticleId $id) => $id->toString() === $articleId))
            ->willReturn($articleData);

        $result = $this->handler->__invoke($query);

        $this->assertInstanceOf(ArticleData::class, $result);
        $this->assertEquals($articleId, $result->id->toString());
        $this->assertEquals('Test Article', $result->title->getValue());
    }

    public function testHandleWithNonExistentArticle(): void
    {
        $articleId = $this->generateArticleId()->getValue();
        $query = new Query($articleId);

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Article not found');

        $this->handler->__invoke($query);
    }
}
