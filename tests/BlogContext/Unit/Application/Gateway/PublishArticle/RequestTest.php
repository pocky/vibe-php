<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\PublishArticle;

use App\BlogContext\Application\Gateway\PublishArticle\Constraint\SeoReadyValidator;
use App\BlogContext\Application\Gateway\PublishArticle\Request;
use App\Tests\BlogContext\Unit\Infrastructure\Identity\ArticleIdGeneratorTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestTest extends TestCase
{
    use ArticleIdGeneratorTrait;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        // Create a custom constraint validator factory that returns a mock for SeoReadyValidator
        $constraintValidatorFactory = new readonly class implements ConstraintValidatorFactoryInterface {
            private ConstraintValidatorFactory $defaultFactory;

            public function __construct()
            {
                $this->defaultFactory = new ConstraintValidatorFactory();
            }

            public function getInstance($constraint): ConstraintValidatorInterface
            {
                $className = $constraint->validatedBy();

                if (SeoReadyValidator::class === $className) {
                    return new MockSeoReadyValidator();
                }

                return $this->defaultFactory->getInstance($constraint);
            }
        };

        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->setConstraintValidatorFactory($constraintValidatorFactory)
            ->getValidator();
    }

    public function testFromDataWithValidArticleId(): void
    {
        $articleId = $this->generateArticleId();
        $data = [
            'articleId' => $articleId->getValue(),
        ];

        $request = Request::fromData($data);

        $this->assertEquals($articleId->getValue(), $request->articleId);
    }

    public function testSymfonyValidationConstraints(): void
    {
        $articleId = $this->generateArticleId();
        $request = new Request(
            articleId: $articleId->getValue()
        );

        $violations = $this->validator->validate($request);
        $this->assertCount(0, $violations);
    }

    public function testValidationFailsForInvalidArticleId(): void
    {
        $request = new Request(
            articleId: 'invalid-uuid'
        );

        $violations = $this->validator->validate($request);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testValidationFailsForEmptyArticleId(): void
    {
        $request = new Request(
            articleId: ''
        );

        $violations = $this->validator->validate($request);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testDataReturnsCorrectArray(): void
    {
        $articleId = $this->generateArticleId();
        $request = Request::fromData([
            'articleId' => $articleId->getValue(),
        ]);

        $expected = [
            'articleId' => $articleId->getValue(),
        ];

        $this->assertEquals($expected, $request->data());
    }
}
