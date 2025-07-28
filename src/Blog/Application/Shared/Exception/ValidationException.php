<?php

declare(strict_types=1);

namespace App\Blog\Application\Shared\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationException extends \InvalidArgumentException
{
    public function __construct(
        private readonly ConstraintViolationListInterface $constraintViolationList,
    ) {
        $messages = [];
        foreach ($this->constraintViolationList as $violation) {
            $messages[] = sprintf(
                '%s: %s',
                $violation->getPropertyPath(),
                $violation->getMessage()
            );
        }

        parent::__construct(
            sprintf('Validation failed: %s', implode('; ', $messages))
        );
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }

    /**
     * Get validation errors as an associative array
     *
     * @return array<string, array<string>>
     */
    public function getErrors(): array
    {
        $errors = [];
        foreach ($this->constraintViolationList as $violation) {
            $propertyPath = $violation->getPropertyPath();
            if (!isset($errors[$propertyPath])) {
                $errors[$propertyPath] = [];
            }

            $errors[$propertyPath][] = (string) $violation->getMessage();
        }

        return $errors;
    }
}
