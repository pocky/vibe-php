<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Gateway\Category\CreateCategory;

use App\Blog\Application\Gateway\Category\CreateCategory\Gateway;
use App\Blog\Application\Gateway\Category\CreateCategory\Request;
use App\Blog\Application\Gateway\Category\CreateCategory\Response;
use App\Blog\Application\Shared\Exception\ValidationException;
use App\Blog\Application\Shared\Generator\CategoryIdGeneratorInterface;
use App\Blog\Domain\Category\CategoryCreator;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Model\Category;
use App\Blog\Domain\Category\Shared\Repository\CategoryWriteRepositoryInterface;
use App\Blog\Domain\Shared\Service\SlugGeneratorInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class GatewayTest extends TestCase
{
    private CategoryWriteRepositoryInterface&MockObject $categoryRepository;

    private CategoryIdGeneratorInterface&MockObject $categoryIdGenerator;

    private ValidatorInterface&MockObject $validator;

    private Gateway $gateway;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryWriteRepositoryInterface::class);
        $this->categoryIdGenerator = $this->createMock(CategoryIdGeneratorInterface::class);
        $slugGenerator = $this->createMock(SlugGeneratorInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        // Use real CategoryCreator with mocked repository
        $categoryCreator = new CategoryCreator($this->categoryRepository);

        $this->gateway = new Gateway(
            $categoryCreator,
            $this->categoryIdGenerator,
            $slugGenerator,
            $this->validator
        );
    }

    #[Test]
    public function validRequest_withValidation_callsNecessaryServices(): void
    {
        $request = new Request(
            name: 'Technology',
            description: 'Technology related articles',
            slug: 'technology',
            parentId: 'parent-123',
            order: 10
        );

        $generatedId = new CategoryId('generated-id-123');

        // Mock validation success
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn(new ConstraintViolationList());

        // Mock ID generation
        $this->categoryIdGenerator
            ->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($generatedId);

        // Mock repository interactions for CategoryCreator
        $this->categoryRepository
            ->expects($this->once())
            ->method('existsWithSlug')
            ->willReturn(false);

        $this->categoryRepository
            ->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(Category::class));

        $result = ($this->gateway)($request);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame('generated-id-123', $result->id);
        $this->assertSame('Technology', $result->name);
        $this->assertSame('technology', $result->slug);
        $this->assertSame('Technology related articles', $result->description);
        $this->assertSame('parent-123', $result->parentId);
        $this->assertSame(10, $result->order);
    }

    #[Test]
    public function invalidRequest_withValidationErrors_throwsValidationException(): void
    {
        $request = new Request(
            name: '', // Invalid empty name
            description: 'Technology related articles'
        );

        $violation = new ConstraintViolation(
            'Category name cannot be blank',
            null,
            [],
            $request,
            'name',
            ''
        );
        $violationList = new ConstraintViolationList([$violation]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($violationList);

        $this->expectException(ValidationException::class);

        ($this->gateway)($request);
    }
}
