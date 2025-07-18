<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $validation_exception_namespace; ?>\ValidationException;

final class <?php echo $class_name . "\n"; ?>
{
    private const array ALLOWED_SCHEMES = ['http', 'https'];
    private const int MAX_LENGTH = 2048;

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

        if (self::MAX_LENGTH < strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => strlen($trimmed),
            ]);
        }

        $parts = parse_url($trimmed);
        if (false === $parts) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.invalid_format');
        }

        if (!isset($parts['scheme']) || !in_array($parts['scheme'], self::ALLOWED_SCHEMES, true)) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.invalid_scheme', [
                'allowed' => implode(', ', self::ALLOWED_SCHEMES),
            ]);
        }

        if (!isset($parts['host']) || '' === $parts['host']) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.missing_host');
        }

        if (!filter_var($trimmed, FILTER_VALIDATE_URL)) {
            throw ValidationException::withTranslationKey('validation.<?php echo $name_snake; ?>.invalid_format');
        }

        $this->value = $trimmed;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getScheme(): string
    {
        $parts = parse_url($this->value);
        return $parts['scheme'] ?? '';
    }

    public function getHost(): string
    {
        $parts = parse_url($this->value);
        return $parts['host'] ?? '';
    }

    public function getPath(): string
    {
        $parts = parse_url($this->value);
        return $parts['path'] ?? '/';
    }

    public function getQuery(): string
    {
        $parts = parse_url($this->value);
        return $parts['query'] ?? '';
    }

    public function withPath(string $path): self
    {
        $parts = parse_url($this->value);
        $parts['path'] = '/' . ltrim($path, '/');
        
        return new self($this->buildUrl($parts));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    private function buildUrl(array $parts): string
    {
        $url = $parts['scheme'] . '://';
        if (isset($parts['user'])) {
            $url .= $parts['user'];
            if (isset($parts['pass'])) {
                $url .= ':' . $parts['pass'];
            }
            $url .= '@';
        }
        $url .= $parts['host'];
        if (isset($parts['port'])) {
            $url .= ':' . $parts['port'];
        }
        $url .= $parts['path'] ?? '/';
        if (isset($parts['query'])) {
            $url .= '?' . $parts['query'];
        }
        if (isset($parts['fragment'])) {
            $url .= '#' . $parts['fragment'];
        }
        
        return $url;
    }
}