<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Command\Author\CreateAuthor;

use App\Blog\Application\Operation\Command\Author\CreateAuthor\Command;
use App\Blog\Application\Operation\Command\Author\CreateAuthor\Handler;
use App\Blog\Domain\Author\AuthorCreator;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Model\Author;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorBio;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorName;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private AuthorCreator&MockObject $creator;

    private Handler $handler;

    protected function setUp(): void
    {
        $this->creator = $this->createMock(AuthorCreator::class);
        $eventBus = $this->createMock(EventBusInterface::class);

        $this->handler = new Handler($this->creator, $eventBus);
    }

    #[Test]
    public function handler_passesIdToCreator_withValidCommand(): void
    {
        // Given
        $authorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');
        $command = new Command(
            authorId: $authorId,
            name: 'John Doe',
            email: 'john@example.com',
            bio: 'A passionate writer'
        );

        $createdAuthor = new Author(
            $authorId,
            new AuthorName('John Doe'),
            new AuthorEmail('john@example.com'),
            new AuthorBio('A passionate writer'),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        // The creator should receive the ID from command, not generate it internally
        $this->creator
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $authorId,
                $this->callback(fn (AuthorName $name): bool => 'John Doe' === $name->getValue()),
                $this->callback(fn (AuthorEmail $email): bool => 'john@example.com' === $email->getValue()),
                $this->callback(fn (AuthorBio $bio): bool => 'A passionate writer' === $bio->getValue())
            )
            ->willReturn($createdAuthor);

        // When
        ($this->handler)($command);
    }

    #[Test]
    public function handler_passesIdToCreator_withEmptyBio(): void
    {
        // Given
        $authorId = new AuthorId('123e4567-e89b-12d3-a456-426614174000');
        $command = new Command(
            authorId: $authorId,
            name: 'Jane Smith',
            email: 'jane@example.com',
            bio: ''
        );

        $createdAuthor = new Author(
            $authorId,
            new AuthorName('Jane Smith'),
            new AuthorEmail('jane@example.com'),
            new AuthorBio(''),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $this->creator
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $authorId,
                $this->callback(fn (AuthorName $name): bool => 'Jane Smith' === $name->getValue()),
                $this->callback(fn (AuthorEmail $email): bool => 'jane@example.com' === $email->getValue()),
                $this->callback(fn (AuthorBio $bio): bool => '' === $bio->getValue())
            )
            ->willReturn($createdAuthor);

        // When
        ($this->handler)($command);
    }
}
