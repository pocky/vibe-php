<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Command\Author\DeleteAuthor;

use App\Blog\Application\Operation\Command\Author\DeleteAuthor\Command;
use App\Blog\Application\Operation\Command\Author\DeleteAuthor\Handler;
use App\Blog\Domain\Author\AuthorDeletor;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    public $repository;

    public $eventBus;

    private Handler $handler;

    protected function setUp(): void
    {
        $deletor = $this->createMock(AuthorDeletor::class);

        $this->handler = new Handler($deletor);
    }

    public function testHandleDeleteAuthorSuccess(): void
    {
        // Given
        $command = new Command(
            authorId: '550e8400-e29b-41d4-a716-446655440000'
        );

        $existingAuthor = \App\Blog\Domain\Author\Shared\Model\Author::create(
            new \App\Blog\Domain\Author\Shared\Identifier\AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new \App\Blog\Domain\Author\Shared\ValueObject\AuthorName('Jane Doe'),
            new \App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail('jane@example.com'),
            new \App\Blog\Domain\Author\Shared\ValueObject\AuthorBio('Bio'));

        // Repository expectations
        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('countArticlesByAuthorId')
            ->willReturn(0); // No articles

        $this->repository->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(\App\Blog\Domain\Author\DeleteAuthor\Model\Author::class));

        $this->eventBus->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(AuthorDeleted::class));

        // When
        ($this->handler)($command);
    }

    public function testHandleDeleteAuthorWithArticlesFails(): void
    {
        // Given
        $command = new Command(
            authorId: '550e8400-e29b-41d4-a716-446655440000'
        );

        $existingAuthor = \App\Blog\Domain\Author\Shared\Model\Author::create(
            new \App\Blog\Domain\Author\Shared\Identifier\AuthorId('550e8400-e29b-41d4-a716-446655440000'),
            new \App\Blog\Domain\Author\Shared\ValueObject\AuthorName('Jane Doe'),
            new \App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail('jane@example.com'),
            new \App\Blog\Domain\Author\Shared\ValueObject\AuthorBio('Bio'));

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($existingAuthor);

        $this->repository->expects($this->once())
            ->method('countArticlesByAuthorId')
            ->willReturn(3); // Has 3 articles

        $this->repository->expects($this->never())
            ->method('remove');

        $this->eventBus->expects($this->never())
            ->method('__invoke');

        // Then
        $this->expectException(\App\Blog\Domain\Author\DeleteAuthor\Exception\AuthorHasArticles::class);
        $this->expectExceptionMessage('Cannot delete author with ID "550e8400-e29b-41d4-a716-446655440000" because they have 3 articles.');

        // When
        ($this->handler)($command);
    }
}
