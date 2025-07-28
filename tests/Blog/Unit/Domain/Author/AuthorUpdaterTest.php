<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Domain\Author;

use App\Blog\Domain\Author\AuthorUpdater;
use App\Blog\Domain\Author\Shared\Event\AuthorUpdated;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Model\Author;
use App\Blog\Domain\Author\Shared\Repository\AuthorWriteRepositoryInterface;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorBio;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AuthorUpdaterTest extends TestCase
{
    private AuthorWriteRepositoryInterface&MockObject $repository;

    private AuthorUpdater $updater;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuthorWriteRepositoryInterface::class);
        $this->updater = new AuthorUpdater($this->repository);
    }

    public function testUpdateAuthorSuccess(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $authorName = new AuthorName('Jane Doe');
        $authorEmail = new AuthorEmail('jane@example.com');
        $authorBio = new AuthorBio('Updated bio');
        new \DateTimeImmutable();

        $existingAuthor = Author::create(
            $authorId,
            new AuthorName('John Doe'),
            new AuthorEmail('john@example.com'),
            new AuthorBio('Original bio')
        );

        // Clear creation events to test only update events
        $existingAuthor->releaseEvents();

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($authorId)
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with($authorEmail)
            ->willReturn(null);

        // When
        $author = ($this->updater)(
            $authorId,
            $authorName,
            $authorEmail,
            $authorBio
        );

        // Then
        $this->assertInstanceOf(Author::class, $author);
        $this->assertSame($authorId->getValue(), $author->id()->getValue());
        $this->assertSame('Jane Doe', $author->name()->getValue());
        $this->assertSame('jane@example.com', $author->email()->getValue());
        $this->assertSame('Updated bio', $author->bio()->getValue());
        // Note: updatedAt is set internally by the domain model

        // Check events
        $events = $author->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(AuthorUpdated::class, $events[0]);
        $this->assertSame($authorId->getValue(), $events[0]->authorId);
    }

    public function testUpdateAuthorNotFoundThrowsException(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $authorName = new AuthorName('Jane Doe');
        $authorEmail = new AuthorEmail('jane@example.com');
        $authorBio = new AuthorBio('Updated bio');
        new \DateTimeImmutable();

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($authorId)
            ->willReturn(null);

        // Then
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Author not found: 550e8400-e29b-41d4-a716-446655440000');

        // When
        ($this->updater)(
            $authorId,
            $authorName,
            $authorEmail,
            $authorBio
        );
    }

    public function testUpdateAuthorWithExistingEmailThrowsException(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $authorName = new AuthorName('Jane Doe');
        $authorEmail = new AuthorEmail('existing@example.com');
        $authorBio = new AuthorBio('Updated bio');
        new \DateTimeImmutable();

        $existingAuthor = Author::create(
            $authorId,
            new AuthorName('John Doe'),
            new AuthorEmail('john@example.com'),
            new AuthorBio('Original bio')
        );

        $anotherAuthor = Author::create(
            new AuthorId('660e8400-e29b-41d4-a716-446655440001'),
            new AuthorName('Another Author'),
            $authorEmail,
            new AuthorBio('Another bio')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($authorId)
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with($authorEmail)
            ->willReturn($anotherAuthor);

        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email "existing@example.com" is already used by another author');

        // When
        ($this->updater)(
            $authorId,
            $authorName,
            $authorEmail,
            $authorBio
        );
    }

    public function testUpdateAuthorKeepingSameEmail(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $authorEmail = new AuthorEmail('john@example.com');
        $authorName = new AuthorName('John Updated');
        $authorBio = new AuthorBio('Updated bio');
        new \DateTimeImmutable();

        $existingAuthor = Author::create(
            $authorId,
            new AuthorName('John Doe'),
            $authorEmail,
            new AuthorBio('Original bio')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($authorId)
            ->willReturn($existingAuthor);

        // findByEmail should NOT be called when keeping the same email
        $this->repository->expects($this->never())
            ->method('findByEmail');

        // When
        $author = ($this->updater)(
            $authorId,
            $authorName,
            $authorEmail,
            $authorBio
        );

        // Then
        $this->assertSame('John Updated', $author->name()->getValue());
        $this->assertSame('john@example.com', $author->email()->getValue());
    }
}
