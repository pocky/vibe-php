<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\CreateArticle;

use App\BlogContext\Application\Gateway\CreateArticle\Request;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testFromDataWithValidData(): void
    {
        $authorId = $this->generateArticleId();
        $data = [
            'title' => 'Test Article',
            'content' => 'This is test content for validation',
            'slug' => 'test-article',
            'status' => 'draft',
            'createdAt' => '2024-01-01T10:00:00+00:00',
            'authorId' => $authorId->getValue(),
        ];

        $request = Request::fromData($data);

        $this->assertEquals('Test Article', $request->title);
        $this->assertEquals('This is test content for validation', $request->content);
        $this->assertEquals('test-article', $request->slug);
        $this->assertEquals('draft', $request->status);
        $this->assertEquals($authorId->getValue(), $request->authorId);
        $this->assertInstanceOf(\DateTimeImmutable::class, $request->createdAt);
    }

    public function testSymfonyValidationConstraints(): void
    {
        $authorId = $this->generateArticleId();
        $request = new Request(
            title: 'Test Article',
            content: 'This is test content for validation',
            slug: 'test-article',
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
            authorId: $authorId->getValue()
        );

        $violations = $this->validator->validate($request);
        $this->assertCount(0, $violations);
    }

    public function testValidationFailsForShortTitle(): void
    {
        $request = new Request(
            title: 'Hi', // Too short
            content: 'This is test content for validation',
            slug: 'test-article',
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
        );

        $violations = $this->validator->validate($request);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testValidationFailsForLongTitle(): void
    {
        $request = new Request(
            title: str_repeat('a', 201), // Too long
            content: 'This is test content for validation',
            slug: 'test-article',
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
        );

        $violations = $this->validator->validate($request);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testValidationFailsForShortContent(): void
    {
        $request = new Request(
            title: 'Test Article',
            content: 'Short', // Too short
            slug: 'test-article',
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
        );

        $violations = $this->validator->validate($request);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testValidationFailsForInvalidSlug(): void
    {
        $request = new Request(
            title: 'Test Article',
            content: 'This is test content for validation',
            slug: 'Invalid_Slug!', // Invalid characters
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
        );

        $violations = $this->validator->validate($request);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testValidationFailsForInvalidStatus(): void
    {
        $request = new Request(
            title: 'Test Article',
            content: 'This is test content for validation',
            slug: 'test-article',
            status: 'invalid', // Invalid status
            createdAt: new \DateTimeImmutable(),
        );

        $violations = $this->validator->validate($request);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testValidationFailsForInvalidAuthorId(): void
    {
        $request = new Request(
            title: 'Test Article',
            content: 'This is test content for validation',
            slug: 'test-article',
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
            authorId: 'invalid-uuid'
        );

        $violations = $this->validator->validate($request);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testIsValidAuthorIdMethod(): void
    {
        $authorId = $this->generateArticleId();
        $request = new Request(
            title: 'Test Article',
            content: 'This is test content for validation',
            slug: 'test-article',
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
            authorId: $authorId->getValue()
        );

        $this->assertTrue($request->isValidAuthorId());

        $invalidRequest = new Request(
            title: 'Test Article',
            content: 'This is test content for validation',
            slug: 'test-article',
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
            authorId: 'invalid-uuid'
        );

        $this->assertFalse($invalidRequest->isValidAuthorId());
    }

    public function testIsValidStatusMethod(): void
    {
        $request = new Request(
            title: 'Test Article',
            content: 'This is test content for validation',
            slug: 'test-article',
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
        );

        $this->assertTrue($request->isValidStatus());

        $invalidRequest = new Request(
            title: 'Test Article',
            content: 'This is test content for validation',
            slug: 'test-article',
            status: 'invalid',
            createdAt: new \DateTimeImmutable(),
        );

        $this->assertFalse($invalidRequest->isValidStatus());
    }

    public function testIsValidSlugMethod(): void
    {
        $request = new Request(
            title: 'Test Article',
            content: 'This is test content for validation',
            slug: 'test-article',
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
        );

        $this->assertTrue($request->isValidSlug());

        $invalidRequest = new Request(
            title: 'Test Article',
            content: 'This is test content for validation',
            slug: 'Invalid_Slug!',
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
        );

        $this->assertFalse($invalidRequest->isValidSlug());
    }

    public function testDataReturnsCorrectArray(): void
    {
        $createdAt = new \DateTimeImmutable('2024-01-01T10:00:00+00:00');
        $authorId = $this->generateArticleId();
        $request = Request::fromData([
            'title' => 'Test Article',
            'content' => 'This is test content for validation',
            'slug' => 'test-article',
            'status' => 'draft',
            'createdAt' => $createdAt->format(\DateTimeInterface::ATOM),
            'authorId' => $authorId->getValue(),
        ]);

        $expected = [
            'title' => 'Test Article',
            'content' => 'This is test content for validation',
            'slug' => 'test-article',
            'status' => 'draft',
            'createdAt' => $createdAt->format(\DateTimeInterface::ATOM),
            'authorId' => $authorId->getValue(),
        ];

        $this->assertEquals($expected, $request->data());
    }
}
