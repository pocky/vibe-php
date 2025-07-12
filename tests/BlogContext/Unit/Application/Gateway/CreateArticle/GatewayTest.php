<?php

declare(strict_types=1);

namespace App\BlogContext\Tests\Unit\Application\Gateway\CreateArticle;

use App\BlogContext\Application\Gateway\CreateArticle\Gateway;
use App\BlogContext\Application\Gateway\CreateArticle\Middleware\Processor;
use App\BlogContext\Application\Gateway\CreateArticle\Request;
use App\BlogContext\Application\Gateway\CreateArticle\Response;
use App\BlogContext\Application\Operation\Command\CreateArticle\Handler;
use App\BlogContext\Domain\CreateArticle\CreatorInterface;
use App\BlogContext\Domain\CreateArticle\DataPersister\Article;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};
use App\BlogContext\Infrastructure\Identity\ArticleIdGenerator;
use App\Shared\Application\Gateway\Instrumentation\GatewayInstrumentation;
use App\Shared\Application\Gateway\Middleware\DefaultValidation;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class GatewayTest extends TestCase
{
    public function testCreateArticleSuccessfully(): void
    {
        // Test with real components for integration testing
        $mockInstrumentation = $this->createMock(GatewayInstrumentation::class);
        $mockValidator = $this->createMock(ValidatorInterface::class);

        // Mock validator to return no violations (valid)
        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(0);
        $mockValidator->method('validate')->willReturn($violations);

        // Mock ArticleIdGenerator
        $mockArticleIdGenerator = $this->createMock(ArticleIdGenerator::class);
        $mockArticleIdGenerator->method('nextIdentity')->willReturn(
            new ArticleId('550e8400-e29b-41d4-a716-446655440001')
        );

        $validation = new DefaultValidation($mockValidator);

        // Create a real Article instance for the test
        $articleInstance = new Article(
            id: new ArticleId('550e8400-e29b-41d4-a716-446655440001'),
            title: new Title('My Test Article'),
            content: new Content('This is test content for the article.'),
            slug: new Slug('my-test-article'),
            status: ArticleStatus::DRAFT,
            createdAt: new \DateTimeImmutable('2024-01-01T10:00:00Z'),
        );

        $mockCreator = $this->createMock(CreatorInterface::class);
        $mockCreator->expects($this->once())
            ->method('__invoke')
            ->willReturn($articleInstance);

        $mockEventBus = $this->createMock(EventBusInterface::class);
        $mockEventBus->expects($this->once())
            ->method('__invoke');

        $handler = new Handler($mockCreator, $mockEventBus);
        $processor = new Processor($handler, $mockArticleIdGenerator);

        $gateway = new Gateway(
            $mockInstrumentation,
            $validation,
            $processor
        );

        $request = Request::fromData([
            'title' => 'My Test Article',
            'content' => 'This is test content for the article.',
            'slug' => 'my-test-article',
            'status' => 'draft',
            'createdAt' => '2024-01-01T10:00:00Z',
            'authorId' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        // When
        $response = ($gateway)($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);

        $data = $response->data();
        $this->assertArrayHasKey('articleId', $data);
        $this->assertArrayHasKey('slug', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('createdAt', $data);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440001', $data['articleId']);
        $this->assertSame('my-test-article', $data['slug']);
        $this->assertSame('draft', $data['status']);
    }

    public function testCreateArticleWithoutAuthor(): void
    {
        // Test with real components for integration testing
        $mockInstrumentation = $this->createMock(GatewayInstrumentation::class);
        $mockValidator = $this->createMock(ValidatorInterface::class);

        // Mock validator to return no violations (valid)
        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(0);
        $mockValidator->method('validate')->willReturn($violations);

        // Mock ArticleIdGenerator
        $mockArticleIdGenerator = $this->createMock(ArticleIdGenerator::class);
        $mockArticleIdGenerator->method('nextIdentity')->willReturn(
            new ArticleId('550e8400-e29b-41d4-a716-446655440001')
        );

        $validation = new DefaultValidation($mockValidator);

        // Create a real Article instance for the test
        $articleInstance = new Article(
            id: new ArticleId('550e8400-e29b-41d4-a716-446655440001'),
            title: new Title('Article Without Author'),
            content: new Content('Content without author.'),
            slug: new Slug('article-without-author'),
            status: ArticleStatus::DRAFT,
            createdAt: new \DateTimeImmutable('2024-01-01T10:00:00Z'),
        );

        $mockCreator = $this->createMock(CreatorInterface::class);
        $mockCreator->expects($this->once())
            ->method('__invoke')
            ->willReturn($articleInstance);

        $mockEventBus = $this->createMock(EventBusInterface::class);
        $mockEventBus->expects($this->once())
            ->method('__invoke');

        $handler = new Handler($mockCreator, $mockEventBus);
        $processor = new Processor($handler, $mockArticleIdGenerator);

        $gateway = new Gateway(
            $mockInstrumentation,
            $validation,
            $processor
        );

        $request = Request::fromData([
            'title' => 'Article Without Author',
            'content' => 'Content without author.',
            'slug' => 'article-without-author',
            'status' => 'draft',
            'createdAt' => '2024-01-01T10:00:00Z',
        ]);

        // When
        $response = ($gateway)($request);

        // Then
        $this->assertInstanceOf(Response::class, $response);

        $data = $response->data();
        $this->assertSame('550e8400-e29b-41d4-a716-446655440001', $data['articleId']);
        $this->assertSame('article-without-author', $data['slug']);
        $this->assertSame('draft', $data['status']);
    }

    public function testRequestValidation(): void
    {
        // Given - Invalid data (empty title)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title is required');

        // When
        Request::fromData([
            'content' => 'Content without title',
        ]);
    }
}
