<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\UpdateAuthor;

use App\BlogContext\Domain\CreateAuthor\Model\Author as CreateAuthorModel;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;
use App\BlogContext\Domain\UpdateAuthor\Event\AuthorUpdated;
use App\BlogContext\Domain\UpdateAuthor\Exception\AuthorNotFound;
use App\BlogContext\Domain\UpdateAuthor\Model\Author;
use App\BlogContext\Domain\UpdateAuthor\Updater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdaterTest extends TestCase
{
    private AuthorRepositoryInterface&MockObject $repository;
    private Updater $updater;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuthorRepositoryInterface::class);
        $this->updater = new Updater($this->repository);
    }

    public function testUpdateAuthorSuccess(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $newName = new AuthorName('Jane Doe');
        $newEmail = new AuthorEmail('jane@example.com');
        $newBio = new AuthorBio('Updated bio');
        $updatedAt = new \DateTimeImmutable();

        $existingAuthor = CreateAuthorModel::create(
            $authorId,
            new AuthorName('John Doe'),
            new AuthorEmail('john@example.com'),
            new AuthorBio('Original bio'),
            new \DateTimeImmutable('2024-01-01')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($authorId)
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with($newEmail)
            ->willReturn(null);

        // When
        $author = ($this->updater)(
            $authorId,
            $newName,
            $newEmail,
            $newBio,
            $updatedAt
        );

        // Then
        $this->assertInstanceOf(Author::class, $author);
        $this->assertEquals($authorId->getValue(), $author->id()->getValue());
        $this->assertEquals('Jane Doe', $author->name()->getValue());
        $this->assertEquals('jane@example.com', $author->email()->getValue());
        $this->assertEquals('Updated bio', $author->bio()->getValue());
        $this->assertEquals($updatedAt, $author->updatedAt());

        // Check events
        $events = $author->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(AuthorUpdated::class, $events[0]);
        $this->assertEquals($authorId->getValue(), $events[0]->authorId());
    }

    public function testUpdateAuthorNotFoundThrowsException(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $newName = new AuthorName('Jane Doe');
        $newEmail = new AuthorEmail('jane@example.com');
        $newBio = new AuthorBio('Updated bio');
        $updatedAt = new \DateTimeImmutable();

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($authorId)
            ->willReturn(null);

        // Then
        $this->expectException(AuthorNotFound::class);
        $this->expectExceptionMessage('Author with ID "550e8400-e29b-41d4-a716-446655440000" was not found.');

        // When
        ($this->updater)(
            $authorId,
            $newName,
            $newEmail,
            $newBio,
            $updatedAt
        );
    }

    public function testUpdateAuthorWithExistingEmailThrowsException(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $newName = new AuthorName('Jane Doe');
        $newEmail = new AuthorEmail('existing@example.com');
        $newBio = new AuthorBio('Updated bio');
        $updatedAt = new \DateTimeImmutable();

        $existingAuthor = CreateAuthorModel::create(
            $authorId,
            new AuthorName('John Doe'),
            new AuthorEmail('john@example.com'),
            new AuthorBio('Original bio'),
            new \DateTimeImmutable('2024-01-01')
        );

        $anotherAuthor = CreateAuthorModel::create(
            new AuthorId('660e8400-e29b-41d4-a716-446655440001'),
            new AuthorName('Another Author'),
            $newEmail,
            new AuthorBio('Another bio'),
            new \DateTimeImmutable('2024-01-02')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($authorId)
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with($newEmail)
            ->willReturn($anotherAuthor);

        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email "existing@example.com" is already used by another author');

        // When
        ($this->updater)(
            $authorId,
            $newName,
            $newEmail,
            $newBio,
            $updatedAt
        );
    }

    public function testUpdateAuthorKeepingSameEmail(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $email = new AuthorEmail('john@example.com');
        $newName = new AuthorName('John Updated');
        $newBio = new AuthorBio('Updated bio');
        $updatedAt = new \DateTimeImmutable();

        $existingAuthor = CreateAuthorModel::create(
            $authorId,
            new AuthorName('John Doe'),
            $email,
            new AuthorBio('Original bio'),
            new \DateTimeImmutable('2024-01-01')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($authorId)
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($existingAuthor);

        // When
        $author = ($this->updater)(
            $authorId,
            $newName,
            $email,
            $newBio,
            $updatedAt
        );

        // Then
        $this->assertEquals('John Updated', $author->name()->getValue());
        $this->assertEquals('john@example.com', $author->email()->getValue());
    }
}
