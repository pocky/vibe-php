<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\CreateArticle\Middleware;

use App\BlogContext\Application\Gateway\CreateArticle\Middleware\Processor;
use App\BlogContext\Application\Gateway\CreateArticle\Request;
use App\BlogContext\Application\Gateway\CreateArticle\Response;
use App\BlogContext\Application\Operation\Command\CreateArticle\HandlerInterface as CreateArticleHandlerInterface;
use App\BlogContext\Domain\Shared\Generator\ArticleIdGeneratorInterface;
use App\BlogContext\Domain\Shared\Service\SlugGeneratorInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use PHPUnit\Framework\TestCase;

final class ProcessorTest extends TestCase
{
    private CreateArticleHandlerInterface $handler;
    private ArticleIdGeneratorInterface $idGenerator;
    private SlugGeneratorInterface $slugGenerator;
    private Processor $processor;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(CreateArticleHandlerInterface::class);
        $this->idGenerator = $this->createMock(ArticleIdGeneratorInterface::class);
        $this->slugGenerator = $this->createMock(SlugGeneratorInterface::class);

        $this->processor = new Processor(
            $this->handler,
            $this->idGenerator,
            $this->slugGenerator
        );
    }

    public function testProcessorGeneratesIdAndSlug(): void
    {
        // Given
        $request = Request::fromData([
            'title' => 'Test Article',
            'content' => 'This is test content',
            'authorId' => 'author-123',
        ]);

        $generatedId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $generatedSlug = new Slug('test-article');

        $this->idGenerator->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($generatedId);

        $this->slugGenerator->expects($this->once())
            ->method('generateFromTitle')
            ->with($this->isInstanceOf(Title::class))
            ->willReturn($generatedSlug);

        $this->handler->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(fn ($command) => '550e8400-e29b-41d4-a716-446655440000' === $command->articleId
                && 'Test Article' === $command->title
                && 'This is test content' === $command->content
                && 'test-article' === $command->slug
                && 'author-123' === $command->authorId));

        // When
        $response = ($this->processor)($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('Article created successfully', $response->message);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $response->articleId);
        $this->assertEquals('test-article', $response->slug);
    }

    public function testProcessorUsesProvidedSlug(): void
    {
        // Given
        $request = Request::fromData([
            'title' => 'Test Article',
            'content' => 'This is test content',
            'slug' => 'custom-slug',
            'authorId' => 'author-123',
        ]);

        $generatedId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');

        $this->idGenerator->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($generatedId);

        $this->slugGenerator->expects($this->never())
            ->method('generateFromTitle');

        $this->handler->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(fn ($command) => 'custom-slug' === $command->slug));

        // When
        $response = ($this->processor)($request);

        // Then
        $this->assertTrue($response->success);
        $this->assertEquals('custom-slug', $response->slug);
    }
}
