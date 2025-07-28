<?php declare(strict_types=1);

echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $validation_exception_namespace; ?>\ValidationException;
use Symfony\Component\Uid\Uuid;

final class <?php echo $class_name; ?> implements \Stringable
{
    public function __construct(
        private string $value {
            set
    {
        if ('' === $value) {
            throw ValidationException::withTranslationKey('validation.<?php echo $entity_snake_case; ?>_id.empty');
        }

        if (!Uuid::isValid($value)) {
            throw ValidationException::withTranslationKey('validation.<?php echo $entity_snake_case; ?>_id.invalid_uuid', [
                'value' => $value,
            ]);
        }

        $this->value = strtolower($value);
    }
        }
    )
    {
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(): self
    {
        return new self((string) Uuid::v7());
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
}
