<?php declare(strict_types=1);

echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $validation_exception_namespace; ?>\ValidationException;

final class <?php echo $class_name; ?> implements \Stringable

{
    private const float MIN_VALUE = 0.0;
    private const float MAX_VALUE = 100.0;

    public function __construct(
        private(set) float $value,
    ) {
        $this->validate();
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    private function validate(): void
    {
        if ($this->value < self::MIN_VALUE) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.too_low', [
                'min_value' => self::MIN_VALUE,
                'actual_value' => $this->value,
            ]);
        }

        if ($this->value > self::MAX_VALUE) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.too_high', [
                'max_value' => self::MAX_VALUE,
                'actual_value' => $this->value,
            ]);
        }
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getAsDecimal(): float
    {
        return $this->value / 100;
    }

    public function getFormatted(int $decimals = 2): string
    {
        return number_format($this->value, $decimals, '.', '') . '%';
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(self $other): self
    {
        return new self($this->value - $other->value);
    }

    public function applyTo(float $amount): float
    {
        return $amount * $this->getAsDecimal();
    }

    public function isZero(): bool
    {
        return 0.0 === $this->value;
    }

    public function isFull(): bool
    {
        return 100.0 === $this->value;
    }

    public function equals(self $other): bool
    {
        // Use epsilon for float comparison
        return abs($this->value - $other->value) < 0.0001;
    }

    public function __toString(): string
    {
        return $this->value . '%';
    }

}
