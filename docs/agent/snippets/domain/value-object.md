# Value Object Template

## Basic Value Object (String-based)

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Domain\Shared\ValueObject;

final class [ValueObject]
{
    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        // Add validation logic here
        if ('' === $this->value) {
            throw new \InvalidArgumentException('[ValueObject] cannot be empty');
        }
        
        // Example: Length validation
        $length = mb_strlen($this->value);
        if ($length < 2 || $length > 100) {
            throw new \InvalidArgumentException('[ValueObject] must be between 2 and 100 characters');
        }
        
        // Example: Format validation
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $this->value)) {
            throw new \InvalidArgumentException('[ValueObject] can only contain letters, numbers and hyphens');
        }
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

## ID Value Object (UUID-based)

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Domain\Shared\ValueObject;

use Symfony\Component\Uid\Uuid;

final class [Entity]Id
{
    public function __construct(
        private(set) string $value,
    ) {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException('Invalid [Entity] ID format');
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::v7()->toRfc4122());
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

## Email Value Object

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Domain\Shared\ValueObject;

final class Email
{
    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        
        $domain = substr($this->value, strrpos($this->value, '@') + 1);
        if (!checkdnsrr($domain, 'MX')) {
            throw new \InvalidArgumentException('Email domain does not exist');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        return substr($this->value, strrpos($this->value, '@') + 1);
    }

    public function getLocalPart(): string
    {
        return substr($this->value, 0, strrpos($this->value, '@'));
    }

    public function equals(self $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

## Money Value Object

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Domain\Shared\ValueObject;

final class Money
{
    public function __construct(
        private(set) int $amount, // Store in cents to avoid float precision issues
        private(set) string $currency,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->amount < 0) {
            throw new \InvalidArgumentException('Money amount cannot be negative');
        }
        
        if (!in_array($this->currency, ['USD', 'EUR', 'GBP'], true)) {
            throw new \InvalidArgumentException('Unsupported currency');
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

    public function getAmountAsFloat(): float
    {
        return $this->amount / 100;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot add money with different currencies');
        }
        
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot subtract money with different currencies');
        }
        
        if ($this->amount < $other->amount) {
            throw new \InvalidArgumentException('Cannot subtract: insufficient amount');
        }
        
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function format(): string
    {
        $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£'];
        $symbol = $symbols[$this->currency] ?? $this->currency;
        
        return sprintf('%s%.2f', $symbol, $this->getAmountAsFloat());
    }
}
```

## Composite Value Object

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\Domain\Shared\ValueObject;

final class Address
{
    public function __construct(
        private(set) string $street,
        private(set) string $city,
        private(set) string $postalCode,
        private(set) string $country,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->street || '' === $this->city || '' === $this->postalCode || '' === $this->country) {
            throw new \InvalidArgumentException('All address fields are required');
        }
        
        if (!preg_match('/^[A-Z]{2}$/', $this->country)) {
            throw new \InvalidArgumentException('Country must be a 2-letter ISO code');
        }
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function equals(self $other): bool
    {
        return $this->street === $other->street
            && $this->city === $other->city
            && $this->postalCode === $other->postalCode
            && $this->country === $other->country;
    }

    public function format(): string
    {
        return sprintf(
            "%s\n%s %s\n%s",
            $this->street,
            $this->postalCode,
            $this->city,
            $this->country
        );
    }
}
```

## PHPUnit Test Template

```php
<?php

declare(strict_types=1);

namespace App\Tests\[Context]Context\Unit\Domain\Shared\ValueObject;

use App\[Context]Context\Domain\Shared\ValueObject\[ValueObject];
use PHPUnit\Framework\TestCase;

final class [ValueObject]Test extends TestCase
{
    public function testCreateValid[ValueObject](): void
    {
        $value = 'valid-value';
        $valueObject = new [ValueObject]($value);
        
        $this->assertEquals($value, $valueObject->getValue());
        $this->assertEquals($value, (string) $valueObject);
    }

    public function testEquality(): void
    {
        $valueObject1 = new [ValueObject]('value');
        $valueObject2 = new [ValueObject]('value');
        $valueObject3 = new [ValueObject]('different');
        
        $this->assertTrue($valueObject1->equals($valueObject2));
        $this->assertFalse($valueObject1->equals($valueObject3));
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function testInvalidValue(string $value, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        
        new [ValueObject]($value);
    }

    public static function invalidValueProvider(): array
    {
        return [
            'empty' => ['', '[ValueObject] cannot be empty'],
            'too short' => ['a', '[ValueObject] must be between 2 and 100 characters'],
            'too long' => [str_repeat('a', 101), '[ValueObject] must be between 2 and 100 characters'],
            'invalid format' => ['invalid@format!', '[ValueObject] can only contain letters, numbers and hyphens'],
        ];
    }
}
```