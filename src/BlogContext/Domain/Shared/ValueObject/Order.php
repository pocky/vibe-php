<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class Order
{
    private const int MIN_VALUE = 0;
    private const int MAX_VALUE = 999999;

    public function __construct(
        private(set) int $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (self::MIN_VALUE > $this->value) {
            throw ValidationException::withTranslationKey('validation.order.too_low', [
                'min_value' => self::MIN_VALUE,
                'actual_value' => $this->value,
            ]);
        }

        if (self::MAX_VALUE < $this->value) {
            throw ValidationException::withTranslationKey('validation.order.too_high', [
                'max_value' => self::MAX_VALUE,
                'actual_value' => $this->value,
            ]);
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(self $other): bool
    {
        return $this->value < $other->value;
    }
}
