# Value Object Snippets

## Basic String Value Object with Property Hooks

```php
use App\[Context]Context\Domain\Shared\Exception\ValidationException;

final class [ValueObject] implements \Stringable
{
    public function __construct(private string $value {
        set {
            $trimmed = trim($value);
            
            if ('' === $trimmed) {
                throw ValidationException::withTranslationKey('validation.[value_object].empty');
            }
            
            // TODO: Add other validation
            
            $this->value = $trimmed;
        }
    })
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
}
```

## UUID ID Value Object with Property Hooks

```php
use App\[Context]Context\Domain\Shared\Exception\ValidationException;
use Symfony\Component\Uid\Uuid;

final class [Entity]Id implements \Stringable
{
    public function __construct(private string $value {
        set {
            if ('' === $value) {
                throw ValidationException::withTranslationKey('validation.[entity]_id.empty');
            }

            if (!Uuid::isValid($value)) {
                throw ValidationException::withTranslationKey('validation.[entity]_id.invalid_uuid', [
                    'value' => $value,
                ]);
            }

            $this->value = strtolower($value);
        }
    })
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
```

## Email Value Object with Property Hooks

```php
use App\[Context]Context\Domain\Shared\Exception\ValidationException;

final class Email implements \Stringable
{
    public function __construct(private string $value {
        set {
            $normalized = trim(strtolower($value));
            
            if ('' === $normalized) {
                throw ValidationException::withTranslationKey('validation.email.empty');
            }
            
            if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withTranslationKey('validation.email.invalid', [
                    'value' => $value,
                ]);
            }
            
            $this->value = $normalized;
        }
    })
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

    public function getDomain(): string
    {
        return substr($this->value, strrpos($this->value, '@') + 1);
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
```

## Money Value Object

```php
use App\[Context]Context\Domain\Shared\Exception\ValidationException;

final class Money implements \Stringable
{
    public function __construct(
        private(set) int $amount, // cents
        private(set) string $currency
    ) {
        if ($this->amount < 0) {
            throw ValidationException::withTranslationKey('validation.money.negative_amount');
        }
    }

    public static function fromFloat(float $amount, string $currency): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw ValidationException::withTranslationKey('validation.money.currency_mismatch');
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function format(): string
    {
        return sprintf('%.2f %s', $this->amount / 100, $this->currency);
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
```

## Test Template

```php
use PHPUnit\Framework\Attributes\{Test, DataProvider};

final class [ValueObject]Test extends TestCase
{
    #[Test]
    public function valid_value_object_creation_stores_value(): void
    {
        $vo = new [ValueObject]('value');
        $this->assertEquals('value', $vo->getValue());
    }

    #[Test]
    public function equals_returns_true_for_same_values(): void
    {
        $vo1 = new [ValueObject]('value');
        $vo2 = new [ValueObject]('value');
        $this->assertTrue($vo1->equals($vo2));
    }

    #[Test]
    #[DataProvider('invalidValueProvider')]
    public function invalid_value_throws_exception(string $value, string $translationKey): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($translationKey);
        new [ValueObject]($value);
    }

    public static function invalidValueProvider(): array
    {
        return [
            'empty' => ['', 'validation.[value_object].empty'],
        ];
    }
}
```