<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Gateway\Author\CreateAuthor\Middleware;

use App\Blog\Application\Gateway\Author\CreateAuthor\Middleware\Processor;
use App\Blog\Application\Gateway\Author\CreateAuthor\Request;
use App\Blog\Application\Gateway\Author\CreateAuthor\Response;
use App\Blog\Application\Operation\Command\Author\CreateAuthor\Command;
use App\Blog\Application\Operation\Command\Author\CreateAuthor\HandlerInterface;
use App\Blog\Application\Shared\Generator\AuthorIdGeneratorInterface;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ProcessorTest extends TestCase
{
    private HandlerInterface&MockObject $handler;

    private AuthorIdGeneratorInterface&MockObject $authorIdGenerator;

    private Processor $processor;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(HandlerInterface::class);
        $this->authorIdGenerator = $this->createMock(AuthorIdGeneratorInterface::class);

        $this->processor = new Processor(
            $this->handler,
            $this->authorIdGenerator
        );
    }

    #[Test]
    public function processor_generatesIdBeforeCommandCreation_passesIdToCommand(): void
    {
        // Given
        $request = new Request(
            name: 'John Doe',
            email: 'john@example.com',
            bio: 'A passionate writer'
        );

        $expectedAuthorId = new AuthorId('550e8400-e29b-41d4-a716-446655440000');

        // ID should be generated before command creation
        $this->authorIdGenerator
            ->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($expectedAuthorId);

        // Command should receive the generated ID
        $this->handler
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(fn (Command $command): bool => $command->authorId->equals($expectedAuthorId)
                && 'John Doe' === $command->name
                && 'john@example.com' === $command->email
                && 'A passionate writer' === $command->bio));

        // When
        $response = ($this->processor)($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->success);
        $this->assertSame($expectedAuthorId->getValue(), $response->authorId);
    }

    #[Test]
    public function processor_withEmptyBio_generatesIdAndCreatesCommand(): void
    {
        // Given
        $request = new Request(
            name: 'Jane Smith',
            email: 'jane@example.com',
            bio: ''
        );

        $expectedAuthorId = new AuthorId('123e4567-e89b-12d3-a456-426614174000');

        $this->authorIdGenerator
            ->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($expectedAuthorId);

        $this->handler
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(fn (Command $command): bool => $command->authorId->equals($expectedAuthorId)
                && 'Jane Smith' === $command->name
                && 'jane@example.com' === $command->email
                && '' === $command->bio));

        // When
        $response = ($this->processor)($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->success);
        $this->assertSame($expectedAuthorId->getValue(), $response->authorId);
    }
}
