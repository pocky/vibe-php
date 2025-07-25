<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\CreateCategory\Middleware;

use App\BlogContext\Application\Gateway\CreateCategory\Middleware\Processor;
use App\BlogContext\Application\Gateway\CreateCategory\Request;
use App\BlogContext\Application\Gateway\CreateCategory\Response;
use App\BlogContext\Application\Operation\Command\CreateCategory\Command;
use App\BlogContext\Application\Operation\Command\CreateCategory\HandlerInterface;
use App\BlogContext\Domain\Shared\Generator\CategoryIdGeneratorInterface;
use App\BlogContext\Domain\Shared\Service\SlugGeneratorInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\Name;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use PHPUnit\Framework\TestCase;

final class ProcessorTest extends TestCase
{
    private HandlerInterface $handler;
    private CategoryIdGeneratorInterface $idGenerator;
    private SlugGeneratorInterface $slugGenerator;
    private Processor $processor;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(HandlerInterface::class);
        $this->idGenerator = $this->createMock(CategoryIdGeneratorInterface::class);
        $this->slugGenerator = $this->createMock(SlugGeneratorInterface::class);

        $this->processor = new Processor(
            $this->handler,
            $this->idGenerator,
            $this->slugGenerator,
        );
    }

    public function testItCreatesACategorySuccessfully(): void
    {
        // Arrange
        $request = new Request(
            name: 'Technology',
            description: 'Tech articles',
        );

        $categoryId = new CategoryId('550e8400-e29b-41d4-a716-446655440000');
        $slug = new Slug('technology');

        $this->idGenerator
            ->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($categoryId);

        $this->slugGenerator
            ->expects($this->once())
            ->method('generateFromName')
            ->with($this->isInstanceOf(Name::class))
            ->willReturn($slug);

        $this->handler
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(fn (Command $command) => '550e8400-e29b-41d4-a716-446655440000' === $command->categoryId
                && 'Technology' === $command->name
                && 'Tech articles' === $command->description
                && 'technology' === $command->slug
                && null === $command->parentCategoryId
                && 0 === $command->order));

        // Act
        $response = ($this->processor)($request);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('Category created successfully', $response->message);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $response->categoryId);
        $this->assertEquals('technology', $response->slug);
    }

    public function testItCreatesACategoryWithParentSuccessfully(): void
    {
        // Arrange
        $request = new Request(
            name: 'PHP',
            description: 'PHP programming',
            parentCategoryId: '660e8400-e29b-41d4-a716-446655440001',
            order: 5,
        );

        $categoryId = new CategoryId('550e8400-e29b-41d4-a716-446655440000');
        $slug = new Slug('php');

        $this->idGenerator
            ->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($categoryId);

        $this->slugGenerator
            ->expects($this->once())
            ->method('generateFromName')
            ->with($this->isInstanceOf(Name::class))
            ->willReturn($slug);

        $this->handler
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(fn (Command $command) => '550e8400-e29b-41d4-a716-446655440000' === $command->categoryId
                && 'PHP' === $command->name
                && 'PHP programming' === $command->description
                && 'php' === $command->slug
                && '660e8400-e29b-41d4-a716-446655440001' === $command->parentCategoryId
                && 5 === $command->order));

        // Act
        $response = ($this->processor)($request);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('Category created successfully', $response->message);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $response->categoryId);
        $this->assertEquals('php', $response->slug);
    }

    public function testItHandlesExceptionGracefully(): void
    {
        // Arrange
        $request = new Request(
            name: 'Technology',
            description: 'Tech articles',
        );

        $categoryId = new CategoryId('550e8400-e29b-41d4-a716-446655440000');

        $this->idGenerator
            ->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($categoryId);

        $this->slugGenerator
            ->expects($this->once())
            ->method('generateFromName')
            ->willThrowException(new \RuntimeException('Slug generation failed'));

        // Act
        $response = ($this->processor)($request);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->success);
        $this->assertEquals('Slug generation failed', $response->message);
        $this->assertNull($response->categoryId);
        $this->assertNull($response->slug);
    }
}
