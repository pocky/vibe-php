<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $validation_exception_namespace; ?>\ValidationException;
use Symfony\Component\Uid\Uuid;

final class <?php echo $class_name . "\n"; ?>
{
    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.<?php echo $entity_snake_case; ?>.id.invalid_uuid');
        }

        if (!Uuid::isValid($this->value)) {
            throw ValidationException::withTranslationKey('validation.<?php echo $entity_snake_case; ?>.id.invalid_uuid');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
