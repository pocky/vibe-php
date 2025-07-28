<?php declare(strict_types=1);

echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $validation_exception_namespace; ?>\ValidationException;

final class <?php echo $class_name; ?> implements \Stringable
{
    // TODO: Add constants for validation rules
    // private const int MIN_LENGTH = 3;
    // private const int MAX_LENGTH = 100;
    // private const string PATTERN = '/^[a-zA-Z0-9]+$/';

    public function __construct(
        private string $value {
            set
    {
        $trimmed = trim($value);

        if ('' === $trimmed) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.empty');
        }

        // TODO: Add other validations
        // Example validations:
        // if (self::MIN_LENGTH > mb_strlen($trimmed)) {
        //     throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.too_short', [
        //         'min_length' => self::MIN_LENGTH,
        //         'actual_length' => mb_strlen($trimmed),
        //     ]);
        // }

        // if (!preg_match(self::PATTERN, $trimmed)) {
        //     throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.invalid_format');
        // }

        $this->value = $trimmed;
    }
        }
    )
    {
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    // TODO: Add business methods as needed
