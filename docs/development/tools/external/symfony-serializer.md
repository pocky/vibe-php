# Symfony Serializer Documentation

This document describes the use of the Symfony Serializer component in the project for serializing and deserializing objects.

## Overview

The Symfony Serializer component transforms data structures between PHP objects and various formats (JSON, XML, CSV). It uses a two-step process: normalization (object to array) and encoding (array to a specific format).

## Installation

The component is installed via Composer:

```bash
composer require symfony/serializer
```

## Official Documentation
- **Main Documentation**: https://symfony.com/doc/current/serializer.html

## Serializer Architecture

### Serialization Process

```
PHP Object 
Normalizer 
Array 
Encoder 
Format (JSON/XML/CSV)
```

### Deserialization Process

```
Format (JSON/XML/CSV) 
Decoder 
Array 
Denormalizer 
PHP Object
```

### Main Components

1.  **Normalizers**: Convert objects to arrays
2.  **Encoders**: Convert arrays to specific formats
3.  **Context**: Configuration for serialization
4.  **Annotations/Attributes**: Metadata to control serialization

## Basic Usage

### Simple Serialization

```php
use Symfony\Component\Serializer\SerializerInterface;

final readonly class UserService
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {}

    public function serializeUser(User $user): string
    {
        return $this->serializer->serialize($user, 'json');
    }

    public function deserializeUser(string $jsonData): User
    {
        return $this->serializer->deserialize($jsonData, User::class, 'json');
    }
}
```

### Example with a Domain Object

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Domain;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class User
{
    public function __construct(
        #[Groups(['user:read', 'user:write'])]
        private UserId $id,

        #[Groups(['user:read', 'user:write'])]
        #[SerializedName('email_address')]
        private Email $email,

        #[Groups(['user:read', 'user:write'])]
        private Name $name,

        #[Groups(['user:read'])]
        private \DateTimeImmutable $createdAt,

        private ?string $password = null, // Never serialized
    ) {}

    // Getters...
    public function id(): UserId { return $this->id; }
    public function email(): Email { return $this->email; }
    public function name(): Name { return $this->name; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
}
```

## Integration with Messenger

### Existing LoggerMiddleware

The project already uses the serializer in `LoggerMiddleware`:

```php
<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\MessageBus;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class LoggerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private NormalizerInterface $normalizer, // 
Uses the normalizer
    ) {}

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->logReceived($envelope);
        $this->logHandled($envelope);

        return $envelope;
    }

    private function logReceived(Envelope $envelope): void
    {
        $result = [
            'type' => 'received',
            'content' => $this->normalizer->normalize($envelope->getMessage()),
            'messageType' => $envelope->getMessage()::class,
        ];

        $this->logger->info('data : ' . json_encode($result));
    }

    private function logHandled(Envelope $envelope): void
    {
        $result = [
            'type' => 'handled',
            'content' => $this->normalizer->normalize($envelope->last(HandledStamp::class)?->getResult()),
            'messageType' => $envelope->getMessage()::class,
        ];

        $this->logger->info('data : ' . json_encode($result));
    }
}
```

### Configuration for Messages

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Command;

use Symfony\Component\Serializer\Attribute\Groups;

final readonly class CreateUser
{
    public function __construct(
        #[Groups(['command:log'])]
        public string $email,

        #[Groups(['command:log'])]
        public string $name,

        // The password is not logged for security
        public ?string $password = null,
    ) {}
}
```

## Available Normalizers

### 1. ObjectNormalizer

The default normalizer for objects:

```php
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

$normalizer = new ObjectNormalizer();
$data = $normalizer->normalize($user);
// Result: ['id' => '...', 'email' => '...', 'name' => '...']
```

### 2. DateTimeNormalizer

For DateTime objects:

```php
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

$normalizer = new DateTimeNormalizer();
$data = $normalizer->normalize(new \DateTimeImmutable());
// Result: '2024-01-15T10:30:00+00:00'
```

### 3. UidNormalizer

For Symfony UUIDs:

```php
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Uid\Uuid;

$normalizer = new UidNormalizer();
$data = $normalizer->normalize(Uuid::v7());
// Result: '01915c8a-b5d2-7034-8c5f-123456789abc'
```

### 4. Custom Normalizer

```php
<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Serializer;

use App\UserContext\Domain\ValueObject\Email;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class EmailNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        if (!$object instanceof Email) {
            throw new \InvalidArgumentException('Expected Email object');
        }

        return $object->value();
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Email;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Email::class => true];
    }
}
```

## Available Encoders

### 1. JsonEncoder

```php
use Symfony\Component\Serializer\Encoder\JsonEncoder;

$encoder = new JsonEncoder();
$json = $encoder->encode(['key' => 'value'], 'json');
// Result: '{"key":"value"}'
```

### 2. XmlEncoder

```php
use Symfony\Component\Serializer\Encoder\XmlEncoder;

$encoder = new XmlEncoder();
$xml = $encoder->encode(['key' => 'value'], 'xml');
// Result: '<?xml version="1.0"?><response><key>value</key></response>'
```

### 3. CsvEncoder

```php
use Symfony\Component\Serializer\Encoder\CsvEncoder;

$encoder = new CsvEncoder();
$csv = $encoder->encode([['name' => 'John', 'age' => 30]], 'csv');
// Result: "name,age\nJohn,30\n"
```

## Serialization Groups

### Defining Groups

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Domain;

use Symfony\Component\Serializer\Attribute\Groups;

final readonly class User
{
    public function __construct(
        #[Groups(['user:read', 'user:admin'])]
        private UserId $id,

        #[Groups(['user:read', 'user:write', 'user:admin'])]
        private Email $email,

        #[Groups(['user:read', 'user:write', 'user:admin'])]
        private Name $name,

        #[Groups(['user:admin'])] // Only for admins
        private \DateTimeImmutable $createdAt,

        #[Groups(['user:admin'])] // Only for admins
        private bool $isActive,
    ) {}
}
```

### Using Groups

```php
final readonly class UserSerializer
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {}

    public function serializeForPublic(User $user): string
    {
        return $this->serializer->serialize($user, 'json', [
            'groups' => ['user:read'],
        ]);
    }

    public function serializeForAdmin(User $user): string
    {
        return $this->serializer->serialize($user, 'json', [
            'groups' => ['user:admin'],
        ]);
    }

    public function serializeForEdit(User $user): string
    {
        return $this->serializer->serialize($user, 'json', [
            'groups' => ['user:read', 'user:write'],
        ]);
    }
}
```

## Controlling Serialization

### Ignoring Properties

```php
use Symfony\Component\Serializer\Attribute\Ignore;

final readonly class User
{
    public function __construct(
        private UserId $id,
        private Email $email,

        #[Ignore] // Never serialized
        private string $password,
    ) {}
}
```

### Custom Name

```php
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class User
{
    public function __construct(
        #[SerializedName('user_id')]
        private UserId $id,

        #[SerializedName('email_address')]
        private Email $email,
    ) {}
}
```

### Default Values

```php
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[DiscriminatorMap(typeProperty: 'type', mapping: [
    'individual' => IndividualUser::class,
    'business' => BusinessUser::class,
])]
abstract readonly class User
{
    // ...
}
```

## Advanced Configuration

### Custom Context

```php
final readonly class UserApiController
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {}

    public function getUser(User $user): JsonResponse
    {
        $context = [
            'groups' => ['user:read'],
            'datetime_format' => 'Y-m-d H:i:s',
            'circular_reference_limit' => 1,
            'object_to_populate' => null,
        ];

        $data = $this->serializer->serialize($user, 'json', $context);

        return new JsonResponse($data, 200, [], true);
    }
}
```

### Handling Circular References

```php
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

$context = [
    AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
        return $object->getId();
    },
];

$data = $serializer->serialize($object, 'json', $context);
```

## Integration with APIs

### Serializer in a Gateway Response

```php
<?php

declare(strict_types=1);

namespace App\UserContext\Application\Gateway;

use App\Shared\Application\Gateway\GatewayResponse;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class UserListResponse implements GatewayResponse
{
    /**
     * @param User[] $users
     */
    public function __construct(
        private array $users,
        private SerializerInterface $serializer,
    ) {}

    public function data(): array
    {
        return [
            'users' => array_map(
                fn(User $user) => $this->serializer->normalize($user, null, [
                    'groups' => ['user:read'],
                ]),
                $this->users
            ),
            'count' => count($this->users),
        ];
    }

    public function toJson(): string
    {
        return $this->serializer->serialize($this->data(), 'json');
    }
}
```

### REST API with Serialization

```php
<?php

declare(strict_types=1);

namespace App\UserContext\UI\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class UserApiController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private UserRepository $userRepository,
    ) {}

    #[Route('/api/users', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = $this->userRepository->findAll();

        $data = $this->serializer->serialize($users, 'json', [
            'groups' => ['user:read'],
        ]);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/users', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            ['groups' => ['user:write']]
        );

        $this->userRepository->save($user);

        $data = $this->serializer->serialize($user, 'json', [
            'groups' => ['user:read'],
        ]);

        return new JsonResponse($data, 201, [], true);
    }
}
```

## Symfony Configuration

### Basic Configuration

```yaml
# config/packages/serializer.yaml
framework:
    serializer:
        enabled: true
        name_converter: 'serializer.name_converter.camel_case_to_snake_case'
        circular_reference_handler: 'serializer.circular_reference_handler'
        default_context:
            datetime_format: 'Y-m-d\TH:i:sP'
            preserve_empty_objects: true
```

### Custom Services

```yaml
# config/services.yaml
services:
    # Custom normalizer
    App\Shared\Infrastructure\Serializer\EmailNormalizer:
        tags: ['serializer.normalizer']

    # Context provider
    App\Shared\Infrastructure\Serializer\UserContextProvider:
        arguments:
            $serializer: '@serializer'
```

## Testing the Serializer

### Normalization Test

```php
<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Serializer;

use App\UserContext\Domain\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

final class UserSerializationTest extends TestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = self::getContainer()->get(SerializerInterface::class);
    }

    public function testUserSerialization(): void
    {
        $user = User::create(
            email: Email::fromString('test@example.com'),
            name: Name::fromString('Test User'),
        );

        $json = $this->serializer->serialize($user, 'json', [
            'groups' => ['user:read'],
        ]);

        $data = json_decode($json, true);

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('email_address', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayNotHasKey('password', $data);
    }

    public function testUserDeserialization(): void
    {
        $json = json_encode([
            'email_address' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $user = $this->serializer->deserialize(
            $json,
            User::class,
            'json',
            ['groups' => ['user:write']]
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('test@example.com', $user->email()->value());
        $this->assertSame('Test User', $user->name()->value());
    }
}
```

## Best Practices

### 
Recommendations

1.  **Serialization Groups**: Use groups to control exposed data
2.  **Explicit Naming**: `SerializedName` for consistent APIs
3.  **Security**: Never serialize passwords or sensitive data
4.  **Performance**: Use appropriate contexts to avoid over-serialization
5.  **Validation**: Validate deserialized data

### 
Configuration

1.  **Custom Normalizers**: For complex Value Objects
2.  **Default Context**: Consistent global configuration
3.  **Circular Reference Handler**: Manage circular references
4.  **DateTime Format**: Uniform format for dates

### 
What to Avoid

1.  **Full Serialization**: Always use groups
2.  **Sensitive Data**: Never serialize passwords, tokens, etc.
3.  **Empty Context**: Always specify groups and options
4.  **Ignoring Performance**: Be mindful of large objects

## Integration with Value Objects

### Example with UserId

```php
<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Serializer;

use App\UserContext\Domain\ValueObject\UserId;
use Symfony\Component\Serializer\Normalizer\{NormalizerInterface, DenormalizerInterface};

final class UserIdNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        if (!$object instanceof UserId) {
            throw new \InvalidArgumentException('Expected UserId object');
        }

        return $object->value();
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): UserId
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException('Expected string for UserId');
        }

        return UserId::fromString($data);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UserId;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === UserId::class && is_string($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [UserId::class => true];
    }
}
```

The Serializer component offers a complete and flexible solution for data transformation, which is essential in a modern API architecture.
