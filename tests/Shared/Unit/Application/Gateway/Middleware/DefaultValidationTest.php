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
    private ValidatorInterface $validator;
    private DefaultValidation $validation;

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
            'authorId' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $expectedResponse = new Response(
            success: true,
            message: 'Article created successfully',
            articleId: '123',
            slug: 'test'
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
        // Given - Using a short title that would normally fail validation
        $request = Request::fromData([
            'title' => 'Hi', // Too short - should fail validation
            'content' => 'This is valid content for the article.',
            'slug' => 'hi',
            'status' => 'draft',
            'authorId' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $next = fn () => new Response(true, 'Article created successfully', '123', 'test');

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

    public function testValidationWithMissingAuthorId(): void
    {
        // Then - Expect exception from Request constructor
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Author ID is required');

        // When - This will throw because authorId is required in Request constructor
        Request::fromData([
            'title' => 'Valid Article Title',
            'content' => 'This is valid content for the article.',
            'slug' => 'valid-article-title',
            'status' => 'draft',
            // authorId is missing
        ]);
    }
}
