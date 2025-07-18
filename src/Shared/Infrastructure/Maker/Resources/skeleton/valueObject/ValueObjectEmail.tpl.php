<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $validation_exception_namespace; ?>\ValidationException;

final class <?php echo $class_name . "\n"; ?>
{
    private const int MAX_LENGTH = 255;

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $trimmed = trim($this->value);

        if ('' === $trimmed) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.empty');
        }

        if (!filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.invalid_format');
        }

        if (self::MAX_LENGTH < strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => strlen($trimmed),
            ]);
        }

        // Normalize to lowercase
        $this->value = strtolower($trimmed);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    public function getLocalPart(): string
    {
        return substr($this->value, 0, strpos($this->value, '@'));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}