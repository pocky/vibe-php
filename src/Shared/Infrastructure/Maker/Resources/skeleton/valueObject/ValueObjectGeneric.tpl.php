<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $validation_exception_namespace; ?>\ValidationException;

final class <?php echo $class_name . "\n"; ?>
{
    // TODO: Add constants for validation rules
    // private const int MIN_LENGTH = 3;
    // private const int MAX_LENGTH = 100;
    // private const string PATTERN = '/^[a-zA-Z0-9]+$/';

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        // TODO: Implement validation logic
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.empty');
        }

        // Example validations:
        // Length validation
        // if (self::MIN_LENGTH > strlen($this->value)) {
        //     throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.too_short', [
        //         'min_length' => self::MIN_LENGTH,
        //         'actual_length' => strlen($this->value),
        //     ]);
        // }

        // Pattern validation
        // if (!preg_match(self::PATTERN, $this->value)) {
        //     throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.invalid_format');
        // }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    // TODO: Add business methods as needed
    // public function toString(): string
    // {
    //     return $this->value;
    // }
}