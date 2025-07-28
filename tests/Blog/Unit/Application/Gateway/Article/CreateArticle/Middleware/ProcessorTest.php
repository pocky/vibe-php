<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Gateway\Article\CreateArticle\Middleware;

use App\Blog\Application\Gateway\Article\CreateArticle\Middleware\Processor;
use App\Blog\Application\Gateway\Article\CreateArticle\Request;
use App\Blog\Application\Gateway\Article\CreateArticle\Response;
use App\Blog\Application\Operation\Command\Article\CreateArticle\Handler as CreateArticleHandler;
use App\Blog\Application\Shared\Generator\ArticleIdGeneratorInterface;
use App\Blog\Domain\Article\ArticleCreator;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\Repository\ArticleWriteRepositoryInterface;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Shared\Service\SlugGeneratorInterface;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\TestCase;

final class ProcessorTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $idGenerator;

    private \PHPUnit\Framework\MockObject\MockObject $slugGenerator;

    private Processor $processor;

    protected function setUp(): void
    {
        $articleRepository = $this->createMock(ArticleWriteRepositoryInterface::class);
        $articleRepository->method('existsWithSlug')->willReturn(false);

        $creator = new ArticleCreator($articleRepository);
        $eventBus = $this->createMock(EventBusInterface::class);
        $handler = new CreateArticleHandler($creator, $eventBus);
        $this->idGenerator = $this->createMock(ArticleIdGeneratorInterface::class);
        $this->slugGenerator = $this->createMock(SlugGeneratorInterface::class);

        $this->processor = new Processor(
            $handler,
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
            'authorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $generatedSlug = new Slug('test-article');

        $this->idGenerator->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($articleId);

        $this->slugGenerator->expects($this->once())
            ->method('generateFromTitle')
            ->with($this->isInstanceOf(Title::class))
            ->willReturn($generatedSlug);

        // No need to expect anything on handler since it's a real instance
        // The test will verify the response instead

        // When
        $gatewayResponse = ($this->processor)($request);

        // Then
        $this->assertInstanceOf(Response::class, $gatewayResponse);
        $this->assertTrue($gatewayResponse->success, 'Response error: ' . $gatewayResponse->message);
        $this->assertSame('Article created successfully', $gatewayResponse->message);
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $gatewayResponse->articleId);
        $this->assertSame('test-article', $gatewayResponse->slug);
    }

    public function testProcessorUsesProvidedSlug(): void
    {
        // Given
        $request = Request::fromData([
            'title' => 'Test Article',
            'content' => 'This is test content',
            'slug' => 'custom-slug',
            'authorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');

        $this->idGenerator->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($articleId);

        $this->slugGenerator->expects($this->never())
            ->method('generateFromTitle');

        // No need to expect anything on handler since it's a real instance

        // When
        $gatewayResponse = ($this->processor)($request);

        // Then
        $this->assertTrue($gatewayResponse->success, 'Response error: ' . $gatewayResponse->message);
        $this->assertEquals('custom-slug', $gatewayResponse->slug);
    }
}
