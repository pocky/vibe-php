<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Gateway\Tag\CreateTag;

use App\Blog\Application\Gateway\Tag\CreateTag\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    #[Test]
    public function validRequest_passesValidation(): void
    {
        $request = new Request(
            name: 'PHP Development',
            slug: 'php-development'
        );

        $constraintViolationList = $this->validator->validate($request);

        $this->assertCount(0, $constraintViolationList);
    }

    #[Test]
    public function blankName_failsValidation(): void
    {
        $request = new Request(
            name: '',
            slug: 'valid-slug'
        );

        $constraintViolationList = $this->validator->validate($request);

        // Empty string triggers both NotBlank and Length constraints
        $this->assertCount(2, $constraintViolationList);

        $messages = [];
        foreach ($constraintViolationList as $violation) {
            if ('name' === $violation->getPropertyPath()) {
                $messages[] = $violation->getMessage();
            }
        }

        $this->assertContains('Tag name cannot be blank', $messages);
        $this->assertContains('Tag name must be at least 2 characters long', $messages);
    }

    #[Test]
    public function nameTooShort_failsValidation(): void
    {
        $request = new Request(
            name: 'A',
            slug: 'valid-slug'
        );

        $constraintViolationList = $this->validator->validate($request);

        $this->assertCount(1, $constraintViolationList);
        $this->assertSame('name', $constraintViolationList[0]->getPropertyPath());
        $this->assertStringContainsString('at least 2 characters', (string) $constraintViolationList[0]->getMessage());
    }

    #[Test]
    public function nameTooLong_failsValidation(): void
    {
        $request = new Request(
            name: str_repeat('a', 101),
            slug: 'valid-slug'
        );

        $constraintViolationList = $this->validator->validate($request);

        $this->assertCount(1, $constraintViolationList);
        $this->assertSame('name', $constraintViolationList[0]->getPropertyPath());
        $this->assertStringContainsString('cannot be longer than 100 characters', (string) $constraintViolationList[0]->getMessage());
    }

    #[Test]
    #[DataProvider('invalidSlugProvider')]
    public function invalidSlug_failsValidation(string $slug, string $expectedMessagePart): void
    {
        $request = new Request(
            name: 'Valid Name',
            slug: $slug
        );

        $constraintViolationList = $this->validator->validate($request);

        $this->assertCount(1, $constraintViolationList);
        $this->assertSame('slug', $constraintViolationList[0]->getPropertyPath());
        $this->assertStringContainsString($expectedMessagePart, (string) $constraintViolationList[0]->getMessage());
    }

    public static function invalidSlugProvider(): \Iterator
    {
        yield 'uppercase letters' => ['PHP-Development', 'lowercase letters, numbers, and hyphens'];
        yield 'spaces' => ['php development', 'lowercase letters, numbers, and hyphens'];
        yield 'special characters' => ['php@development', 'lowercase letters, numbers, and hyphens'];
        yield 'starts with hyphen' => ['-php-development', 'lowercase letters, numbers, and hyphens'];
        yield 'ends with hyphen' => ['php-development-', 'lowercase letters, numbers, and hyphens'];
        yield 'consecutive hyphens' => ['php--development', 'lowercase letters, numbers, and hyphens'];
        yield 'too long' => [str_repeat('a', 151), 'cannot be longer than 150 characters'];
    }

    #[Test]
    public function nullSlug_passesValidation(): void
    {
        $request = new Request(
            name: 'Valid Name',
            slug: null
        );

        $constraintViolationList = $this->validator->validate($request);

        $this->assertCount(0, $constraintViolationList);
    }

    #[Test]
    public function multipleViolations_allReported(): void
    {
        $request = new Request(
            name: '',
            slug: 'INVALID SLUG!'
        );

        $constraintViolationList = $this->validator->validate($request);

        // Empty name triggers 2 violations (NotBlank + Length) + invalid slug = 3 total
        $this->assertCount(3, $constraintViolationList);

        $propertyPaths = [];
        foreach ($constraintViolationList as $violation) {
            $propertyPaths[] = $violation->getPropertyPath();
        }

        $this->assertContains('name', $propertyPaths);
        $this->assertContains('slug', $propertyPaths);
    }
}
