<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\PublishArticle;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Mock SeoReadyValidator for unit tests
 * Always passes validation
 */
final class MockSeoReadyValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        // Always pass validation in unit tests
    }
}
