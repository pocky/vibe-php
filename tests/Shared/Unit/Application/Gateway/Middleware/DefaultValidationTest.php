<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Application\Gateway\Middleware;

use App\BlogContext\Application\Gateway\CreateArticle\Request;
use App\BlogContext\Application\Gateway\CreateArticle\Response;
use App\Shared\Application\Gateway\Middleware\DefaultValidation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DefaultValidationTest extends TestCase
{
    private DefaultValidation $validation;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->validation = new DefaultValidation($this->validator);
    }

    public function testValidationPassesWithValidData(): void
    {
        // Given
        $request = Request::fromData([
            'title' => 'Valid Article Title',
            'content' => 'This is valid content for the article.',
            'slug' => 'valid-article-title',
            'status' => 'draft',
            'createdAt' => '2024-01-01T10:00:00Z',
            'authorId' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $expectedResponse = new Response(
            articleId: '123',
            slug: 'test',
            status: 'draft',
            createdAt: new \DateTimeImmutable()
        );

        $next = fn () => $expectedResponse;

        // Mock validator to return no violations (valid)
        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($violations);

        // When
        $response = ($this->validation)($request, $next);

        // Then
        $this->assertSame($expectedResponse, $response);
    }

    public function testValidationFailsWithInvalidData(): void
    {
        // Given
        $request = Request::fromData([
            'title' => 'Hi', // Too short - should fail validation
            'content' => 'This is valid content for the article.',
            'slug' => 'hi',
            'status' => 'draft',
            'createdAt' => '2024-01-01T10:00:00Z',
        ]);

        $next = fn () => new Response('123', 'test', 'draft', new \DateTimeImmutable());

        // Mock validator to return violations (invalid)
        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->expects($this->once())
            ->method('count')
            ->willReturn(1); // Has violations

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($violations);

        // Then
        $this->expectException(ValidationFailedException::class);

        // When
        ($this->validation)($request, $next);
    }

    public function testValidationWithNullAuthorId(): void
    {
        // Given
        $request = Request::fromData([
            'title' => 'Valid Article Title',
            'content' => 'This is valid content for the article.',
            'slug' => 'valid-article-title',
            'status' => 'draft',
            'createdAt' => '2024-01-01T10:00:00Z',
            // authorId is null
        ]);

        $expectedResponse = new Response(
            articleId: '123',
            slug: 'test',
            status: 'draft',
            createdAt: new \DateTimeImmutable()
        );

        $next = fn () => $expectedResponse;

        // Mock validator to return no violations (valid)
        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($violations);

        // When
        $response = ($this->validation)($request, $next);

        // Then
        $this->assertSame($expectedResponse, $response);
    }
}
