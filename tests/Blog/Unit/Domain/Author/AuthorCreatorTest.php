<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Domain\Author;

use App\Blog\Domain\Author\AuthorCreator;
use App\Blog\Domain\Author\Shared\Event\AuthorCreated;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Model\Author;
use App\Blog\Domain\Author\Shared\Repository\AuthorWriteRepositoryInterface;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorBio;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AuthorCreatorTest extends TestCase
{
    private AuthorWriteRepositoryInterface&MockObject $repository;

    private AuthorCreator $creator;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuthorWriteRepositoryInterface::class);
        // New AuthorCreator only needs repository, no ID generator
        $this->creator = new AuthorCreator($this->repository);
    }

    public function testCreateAuthorSuccess(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $authorName = new AuthorName('John Doe');
        $authorEmail = new AuthorEmail('john@example.com');
        $authorBio = new AuthorBio('A passionate writer');

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with($authorEmail)
            ->willReturn(null);

        $this->repository->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(Author::class));

        // When
        $author = ($this->creator)(
            $authorId,
            $authorName,
            $authorEmail,
            $authorBio
        );

        // Then
        $this->assertInstanceOf(Author::class, $author);
        $this->assertSame($authorId->getValue(), $author->id()->getValue());
        $this->assertSame('John Doe', $author->name()->getValue());
        $this->assertSame('john@example.com', $author->email()->getValue());
        $this->assertSame('A passionate writer', $author->bio()->getValue());

        // Check events
        $events = $author->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(AuthorCreated::class, $events[0]);
        $this->assertSame($author->id()->getValue(), $events[0]->authorId);
    }

    public function testCreateAuthorWithDuplicateEmailThrowsException(): void
    {
        // Given
        $authorId = new AuthorId('660e8400-e29b-41d4-a716-446655440001');
        $authorName = new AuthorName('John Doe');
        $authorEmail = new AuthorEmail('existing@example.com');
        $authorBio = new AuthorBio('Bio');

        // Create a real Author instance instead of mocking
        $existingAuthor = Author::create(
            new AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new AuthorName('Existing Author'),
            $authorEmail,
            new AuthorBio('Existing bio')
        );

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with($authorEmail)
            ->willReturn($existingAuthor);

        // Then
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Author with email "existing@example.com" already exists');

        // When
        ($this->creator)(
            $authorId,
            $authorName,
            $authorEmail,
            $authorBio
        );
    }

    public function testCreateAuthorWithEmptyBio(): void
    {
        // Given
        $authorId = new AuthorId('660e8400-e29b-41d4-a716-446655440001');
        $authorName = new AuthorName('Jane Smith');
        $authorEmail = new AuthorEmail('jane@example.com');
        $authorBio = new AuthorBio(''); // Empty bio

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with($authorEmail)
            ->willReturn(null);

        $this->repository->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(Author::class));

        // When
        $author = ($this->creator)(
            $authorId,
            $authorName,
            $authorEmail,
            $authorBio
        );

        // Then
        $this->assertSame('', $author->bio()->getValue());
    }
}
