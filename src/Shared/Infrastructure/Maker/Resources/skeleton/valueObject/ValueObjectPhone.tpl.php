<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $validation_exception_namespace; ?>\ValidationException;

final class <?php echo $class_name . "\n"; ?>
{
    // E.164 format: +[country code][number] (max 15 digits)
    private const string PATTERN = '/^\+[1-9]\d{1,14}$/';

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        // Remove all non-digit characters except + at the beginning
        $cleaned = preg_replace('/[^\d+]/', '', $this->value);
        $cleaned = preg_replace('/\++/', '+', $cleaned ?? '');

        if ('' === $cleaned || '+' === $cleaned) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.empty');
        }

        // Ensure it starts with +
        if (!str_starts_with($cleaned, '+')) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.missing_country_code');
        }

        if (!preg_match(self::PATTERN, $cleaned)) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.invalid_format');
        }

        $this->value = $cleaned;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getCountryCode(): string
    {
        // Simple extraction - in reality would need a proper library
        if (str_starts_with($this->value, '+1')) {
            return '+1'; // US/Canada
        }
        if (str_starts_with($this->value, '+44')) {
            return '+44'; // UK
        }
        if (str_starts_with($this->value, '+33')) {
            return '+33'; // France
        }
        // Add more as needed
        
        // Default: assume first 2-3 digits after + are country code
        preg_match('/^\+(\d{1,3})/', $this->value, $matches);
        return '+' . ($matches[1] ?? '');
    }

    public function getFormatted(): string
    {
        // Basic formatting - in reality would use a proper library
        $countryCode = $this->getCountryCode();
        $number = substr($this->value, strlen($countryCode));
        
        return $countryCode . ' ' . $number;
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
