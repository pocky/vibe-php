<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\CreateAuthor;

use App\BlogContext\Domain\CreateAuthor\Creator;
use App\BlogContext\Domain\CreateAuthor\Event\AuthorCreated;
use App\BlogContext\Domain\CreateAuthor\Exception\AuthorAlreadyExists;
use App\BlogContext\Domain\CreateAuthor\Model\Author;
use App\BlogContext\Domain\Shared\Generator\AuthorIdGeneratorInterface;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreatorTest extends TestCase
{
    private AuthorRepositoryInterface&MockObject $repository;
    private AuthorIdGeneratorInterface&MockObject $idGenerator;
    private Creator $creator;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuthorRepositoryInterface::class);
        $this->idGenerator = $this->createMock(AuthorIdGeneratorInterface::class);
        $this->creator = new Creator($this->repository, $this->idGenerator);
    }

    public function testCreateAuthorSuccess(): void
    {
        // Given
        $name = new AuthorName('John Doe');
        $email = new AuthorEmail('john@example.com');
        $bio = new AuthorBio('A passionate writer');
        $createdAt = new \DateTimeImmutable();

        $expectedId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->idGenerator->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($expectedId);

        // When
        $author = ($this->creator)(
            $name,
            $email,
            $bio,
            $createdAt
        );

        // Then
        $this->assertInstanceOf(Author::class, $author);
        $this->assertEquals($expectedId->getValue(), $author->id()->getValue());
        $this->assertEquals('John Doe', $author->name()->getValue());
        $this->assertEquals('john@example.com', $author->email()->getValue());
        $this->assertEquals('A passionate writer', $author->bio()->getValue());
        $this->assertEquals($createdAt, $author->createdAt());
        $this->assertEquals($createdAt, $author->updatedAt());

        // Check events
        $events = $author->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(AuthorCreated::class, $events[0]);
        $this->assertEquals($author->id()->getValue(), $events[0]->authorId());
    }

    public function testCreateAuthorWithDuplicateEmailThrowsException(): void
    {
        // Given
        $name = new AuthorName('John Doe');
        $email = new AuthorEmail('existing@example.com');
        $bio = new AuthorBio('Bio');
        $createdAt = new \DateTimeImmutable();

        // Create a real Author instance instead of mocking
        $existingAuthor = Author::create(
            new AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new AuthorName('Existing Author'),
            $email,
            new AuthorBio('Existing bio'),
            new \DateTimeImmutable()
        );

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($existingAuthor);

        // Then
        $this->expectException(AuthorAlreadyExists::class);
        $this->expectExceptionMessage('Author with email "existing@example.com" already exists.');

        // When
        ($this->creator)(
            $name,
            $email,
            $bio,
            $createdAt
        );
    }

    public function testCreateAuthorWithEmptyBio(): void
    {
        // Given
        $name = new AuthorName('Jane Smith');
        $email = new AuthorEmail('jane@example.com');
        $bio = new AuthorBio(''); // Empty bio
        $createdAt = new \DateTimeImmutable();

        $expectedId = new AuthorId('660e8400-e29b-41d4-a716-446655440001');

        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->idGenerator->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($expectedId);

        // When
        $author = ($this->creator)(
            $name,
            $email,
            $bio,
            $createdAt
        );

        // Then
        $this->assertEquals('', $author->bio()->getValue());
    }
}
