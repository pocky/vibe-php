<?php declare(strict_types=1);

echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $validation_exception_namespace; ?>\ValidationException;

final class <?php echo $class_name; ?> implements \Stringable

{
    private const array SUPPORTED_CURRENCIES = ['USD', 'EUR', 'GBP', 'JPY', 'CHF', 'CAD', 'AUD'];

    public function __construct(
        private(set) int $amount, // Amount in cents/smallest unit
        private(set) string $currency,
    ) {
        $this->validate();
    }

    public static function fromAmountAndCurrency(int $amount, string $currency): self
    {
        return new self($amount, $currency);
    }

    private function validate(): void
    {
        if ($this->amount < 0) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.negative_amount');
        }

        $upperCurrency = strtoupper($this->currency);
        if (!in_array($upperCurrency, self::SUPPORTED_CURRENCIES, true)) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.unsupported_currency', [
                'currency' => $this->currency,
                'supported' => implode(', ', self::SUPPORTED_CURRENCIES),
            ]);
        }

        $this->currency = $upperCurrency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getFormattedAmount(): string
    {
        return number_format($this->amount / 100, 2, '.', '');
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.currency_mismatch');
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.currency_mismatch');
        }

        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->amount * $factor), $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return number_format($this->amount / 100, 2, '.', '') . ' ' . $this->currency;
    }

}
