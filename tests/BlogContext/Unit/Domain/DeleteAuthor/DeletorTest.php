<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\DeleteAuthor;

use App\BlogContext\Domain\CreateAuthor\Model\Author as CreateAuthorModel;
use App\BlogContext\Domain\DeleteAuthor\Deletor;
use App\BlogContext\Domain\DeleteAuthor\Event\AuthorDeleted;
use App\BlogContext\Domain\DeleteAuthor\Exception\AuthorHasArticles;
use App\BlogContext\Domain\DeleteAuthor\Exception\AuthorNotFound;
use App\BlogContext\Domain\DeleteAuthor\Model\Author;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeletorTest extends TestCase
{
    private AuthorRepositoryInterface&MockObject $repository;
    private Deletor $deletor;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuthorRepositoryInterface::class);
        $this->deletor = new Deletor($this->repository);
    }

    public function testDeleteAuthorSuccess(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $deletedAt = new \DateTimeImmutable();

        $existingAuthor = CreateAuthorModel::create(
            $authorId,
            new AuthorName('John Doe'),
            new AuthorEmail('john@example.com'),
            new AuthorBio('Bio'),
            new \DateTimeImmutable('2024-01-01')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($authorId)
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('countArticlesByAuthorId')
            ->with($authorId)
            ->willReturn(0);

        // When
        $author = ($this->deletor)($authorId, $deletedAt);

        // Then
        $this->assertInstanceOf(Author::class, $author);
        $this->assertEquals($authorId->getValue(), $author->id()->getValue());
        $this->assertEquals($deletedAt, $author->deletedAt());

        // Check events
        $events = $author->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(AuthorDeleted::class, $events[0]);
        $this->assertEquals($authorId->getValue(), $events[0]->authorId());
        $this->assertEquals($deletedAt, $events[0]->deletedAt());
    }

    public function testDeleteAuthorNotFoundThrowsException(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $deletedAt = new \DateTimeImmutable();

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($authorId)
            ->willReturn(null);

        // Then
        $this->expectException(AuthorNotFound::class);
        $this->expectExceptionMessage('Author with ID "550e8400-e29b-41d4-a716-446655440000" was not found.');

        // When
        ($this->deletor)($authorId, $deletedAt);
    }

    public function testDeleteAuthorWithArticlesThrowsException(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $deletedAt = new \DateTimeImmutable();

        $existingAuthor = CreateAuthorModel::create(
            $authorId,
            new AuthorName('John Doe'),
            new AuthorEmail('john@example.com'),
            new AuthorBio('Bio'),
            new \DateTimeImmutable('2024-01-01')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($authorId)
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('countArticlesByAuthorId')
            ->with($authorId)
            ->willReturn(5); // Author has 5 articles

        // Then
        $this->expectException(AuthorHasArticles::class);
        $this->expectExceptionMessage('Cannot delete author with ID "550e8400-e29b-41d4-a716-446655440000" because they have 5 articles.');

        // When
        ($this->deletor)($authorId, $deletedAt);
    }
}
