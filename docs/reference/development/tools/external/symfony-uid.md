# Symfony UID Component

This document describes the use of the Symfony UID component in the project for generating unique identifiers.

## Installation

The component is installed via Composer:

```bash
composer require symfony/uid
```

## Overview

The Symfony UID component provides utilities for working with unique identifiers (UIDs). It supports:

- **UUIDs**: Universally Unique Identifiers
- **ULIDs**: Lexicographically Sortable Identifiers

## Available UUID Types

### UUID v1 (Time-based)
- Based on timestamp and MAC address
- Can reveal information about the host

### UUID v3 (Name-based MD5)
- Generated from a namespace and a name
- Uses MD5 (less recommended)

### UUID v4 (Random)
- Completely random
- The most commonly used

### UUID v5 (Name-based SHA-1)
- Generated from a namespace and a name
- Uses SHA-1

### UUID v6 (Lexicographically sortable)
- Improved version of v1
- Lexicographically sortable

### UUID v7 (UNIX timestamp-based) ⭐ **RECOMMENDED**
- Based on the UNIX timestamp
- Better entropy than v1
- **Used in our project**

### UUID v8 (Custom)
- Custom implementation

## Implementation in the project

### UuidGenerator

Our generator uses UUID v7 for its advantages:

```php
<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Generator;

use Symfony\Component\Uid\Uuid;

final class UuidGenerator implements GeneratorInterface
{
    #[
Override]
    public static function generate(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
```

### Advantages of UUID v7

1.  **Sortable**: Generated UUIDs are chronologically sortable
2.  **Performance**: Better database performance
3.  **Entropy**: Good entropy while maintaining temporal order
4.  **Standard**: RFC 4122 compatible format

## Usage

### Simple Generation

```php
use App\Shared\Infrastructure\Generator\UuidGenerator;

$uuid = UuidGenerator::generate();
// Example: 01915c8a-b5d2-7034-8c5f-123456789abc
```

### Direct Generation with Symfony UID

```php
use Symfony\Component\Uid\Uuid;

// UUID v7 (recommended)
$uuid = Uuid::v7();
echo $uuid->toRfc4122(); // 01915c8a-b5d2-7034-8c5f-123456789abc

// UUID v4 (random)
$uuid = Uuid::v4();
echo $uuid->toRfc4122(); // 550e8400-e29b-41d4-a716-446655440000

// ULID
use Symfony\Component\Uid\Ulid;
$ulid = new Ulid();
echo $ulid; // 01AN4Z07BY79KA1307SR9X4MV3
```

### Output Formats

```php
$uuid = Uuid::v7();

// RFC 4122 format (recommended)
echo $uuid->toRfc4122(); // 01915c8a-b5d2-7034-8c5f-123456789abc

// base32 format
echo $uuid->toBase32(); // 0C7QCE7TGTAYY3J1ESA9X4MV30

// base58 format
echo $uuid->toBase58(); // SmZjG8Z8kSKSC1n8nVfgDy

// binary format
echo $uuid->toBinary(); // Binary representation
```

## Doctrine Integration

### Doctrine Types

The component provides Doctrine types for seamless integration:

```php
// In an entity
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    public function __construct()
    {
        $this->id = Uuid::v7();
    }
}
```

### Doctrine Configuration

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        types:
            uuid: 'Symfony\Component\Uid\Doctrine\UuidType'
            ulid: 'Symfony\Component\Uid\Doctrine\UlidType'
```

## ULIDs (Alternative to UUIDs)

### ULID Characteristics

- **128 bits** like UUIDs
- **26-character** representation
- **Lexicographically sortable**
- **Monotonic** within the same millisecond

### Using ULIDs

```php
use Symfony\Component\Uid\Ulid;

$ulid = new Ulid();
echo $ulid; // 01AN4Z07BY79KA1307SR9X4MV3

// From timestamp
$ulid = Ulid::fromDateTime(new \DateTime());

// Parsing
$ulid = Ulid::fromString('01AN4Z07BY79KA1307SR9X4MV3');
```

## Console Commands

The component provides commands to generate and inspect UIDs:

```bash
# Generate UUIDs
php bin/console debug:uuid

# Generate ULIDs
php bin/console debug:ulid

# Inspect a UID
php bin/console debug:uid 01915c8a-b5d2-7034-8c5f-123456789abc
```

## Best Practices

### ✅ Recommendations

1.  **Use UUID v7** for new projects
2.  **Avoid UUID v1** (can reveal information)
3.  **Use Doctrine types** for ORM integration
4.  **Consider ULIDs** for cases requiring sorting

### ⚠️ Precautions

1.  **Primary key performance**: UUIDs as primary keys can impact performance
2.  **Storage size**: UUIDs take up more space than integers
3.  **Indexing**: Consider the impact on database indexes

### 🚫 What to Avoid

1.  **UUID v1 in production** (host information)
2.  **UUID v3** (deprecated MD5)
3.  **Unnecessary format conversion** (keep the native format when possible)

## Migration from Ramsey/UUID

If you are migrating from ramsey/uuid:

```php
// Before (Ramsey)
use Ramsey\Uuid\Uuid;
$uuid = Uuid::uuid4()->toString();

// After (Symfony)
use Symfony\Component\Uid\Uuid;
$uuid = Uuid::v7()->toRfc4122();
```

### Advantages of Migration

1.  **Native integration** with the Symfony ecosystem
2.  **Doctrine types included**
3.  **Console commands**
4.  **Better performance** with UUID v7
5.  **Maintained** by the Symfony team

## References

- [Official Symfony UID Documentation](https://symfony.com/doc/current/components/uid.html)
- [RFC 4122 - UUID Standard](https://tools.ietf.org/html/rfc4122)
- [ULID Specification](https://github.com/ulid/spec)
