# API Agent Patterns

## Resource Patterns

### API Resource Pattern
```php
namespace App\{Context}Context\UI\Api\Rest\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;

#[ApiResource(
    shortName: '{Entity}',
    operations: [
        new Get(
            uriTemplate: '/{entities}/{id}',
            provider: Get{Entity}Provider::class,
        ),
        new GetCollection(
            uriTemplate: '/{entities}',
            provider: List{Entities}Provider::class,
        ),
        new Post(
            uriTemplate: '/{entities}',
            processor: Create{Entity}Processor::class,
        ),
        new Put(
            uriTemplate: '/{entities}/{id}',
            provider: Get{Entity}Provider::class,
            processor: Update{Entity}Processor::class,
        ),
        new Delete(
            uriTemplate: '/{entities}/{id}',
            processor: Delete{Entity}Processor::class,
        ),
    ],
)]
final class {Entity}Resource
{
    public function __construct(
        public ?string $id = null,
        // Resource properties
    ) {}
}
```

### State Provider Pattern (Single Item)
```php
namespace App\{Context}Context\UI\Api\Rest\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

final readonly class Get{Entity}Provider implements ProviderInterface
{
    public function __construct(
        private Get{Entity}Gateway $gateway,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['id'])) {
            return null;
        }

        try {
            $request = Get{Entity}Request::fromData(['id' => $uriVariables['id']]);
            $response = ($this->gateway)($request);
            
            $data = $response->data();
            return isset($data['{entity}']) ? $this->transformToResource($data['{entity}']) : null;
        } catch (GatewayException | \RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return null;
            }
            throw $e;
        }
    }
    
    private function transformToResource(array $data): {Entity}Resource
    {
        return new {Entity}Resource(
            id: $data['id'],
            // Map properties
        );
    }
}
```

### State Provider Pattern (Collection)
```php
namespace App\{Context}Context\UI\Api\Rest\Provider;

final readonly class List{Entities}Provider implements ProviderInterface
{
    public function __construct(
        private List{Entities}Gateway $gateway,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $filters = $context['filters'] ?? [];
        
        $request = List{Entities}Request::fromData([
            'page' => (int) ($filters['page'] ?? 1),
            'limit' => (int) ($filters['itemsPerPage'] ?? 20),
            // Map other filters
        ]);
        
        $response = ($this->gateway)($request);
        
        $data = $response->data();
        $items = $data['{entities}'] ?? [];
        
        return array_map(
            fn (array $item) => $this->transformToResource($item),
            $items
        );
    }
}
```

### State Processor Pattern (Create)
```php
namespace App\{Context}Context\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

final readonly class Create{Entity}Processor implements ProcessorInterface
{
    public function __construct(
        private Create{Entity}Gateway $gateway,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var {Entity}Resource $data */
        try {
            $request = Create{Entity}Request::fromData([
                // Map resource to request
            ]);

            $response = ($this->gateway)($request);
            $responseData = $response->data();

            return new {Entity}Resource(
                id: $responseData['{entity}Id'],
                // Map response to resource
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (GatewayException | \RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                throw new ConflictHttpException('Resource already exists', $e);
            }
            throw $e;
        }
    }
}
```

### State Processor Pattern (Update)
```php
namespace App\{Context}Context\UI\Api\Rest\Processor;

final readonly class Update{Entity}Processor implements ProcessorInterface
{
    public function __construct(
        private Update{Entity}Gateway $gateway,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var {Entity}Resource $data */
        try {
            $request = Update{Entity}Request::fromData([
                '{entity}Id' => $uriVariables['id'],
                // Map resource to request
            ]);

            $response = ($this->gateway)($request);
            
            return $data; // Or transform response
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('Resource not found', 404, $e);
            }
            throw $e;
        }
    }
}
```

### State Processor Pattern (Delete)
```php
namespace App\{Context}Context\UI\Api\Rest\Processor;

final readonly class Delete{Entity}Processor implements ProcessorInterface
{
    public function __construct(
        private Delete{Entity}Gateway $gateway,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        try {
            $request = Delete{Entity}Request::fromData([
                '{entity}Id' => $uriVariables['id'],
            ]);

            ($this->gateway)($request);
            
            return null;
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('Resource not found', 404, $e);
            }
            throw $e;
        }
    }
}
```

## Filter Patterns

### Search Filter Pattern
```php
namespace App\{Context}Context\UI\Api\Rest\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

final class {Entity}SearchFilter extends AbstractFilter
{
    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        if ($property === 'search') {
            $alias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->andWhere("{$alias}.title LIKE :search OR {$alias}.content LIKE :search")
                ->setParameter('search', "%{$value}%");
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'search' => [
                'property' => 'search',
                'type' => 'string',
                'required' => false,
                'description' => 'Search in multiple fields',
            ],
        ];
    }
}
```

## Validation Patterns

### Resource Validation
```php
use Symfony\Component\Validator\Constraints as Assert;

final class {Entity}Resource
{
    #[Assert\NotBlank(groups: ['create'])]
    #[Assert\Length(min: 3, max: 200)]
    public ?string $title = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\Choice(choices: ['draft', 'published', 'archived'])]
    public ?string $status = null;

    #[Assert\Valid]
    public ?AddressResource $address = null;
}
```

## Security Patterns

### Operation Security
```php
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('VIEW', object)",
        ),
        new Put(
            security: "is_granted('EDIT', object)",
        ),
        new Delete(
            security: "is_granted('DELETE', object) or is_granted('ROLE_ADMIN')",
        ),
    ],
)]
```

### Custom Voter
```php
namespace App\{Context}Context\UI\Api\Rest\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class {Entity}Voter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof {Entity}Resource;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // Implement authorization logic
    }
}
```

## Error Handling Patterns

### Exception Transformation
```php
trait ExceptionTransformerTrait
{
    private function transformException(\Throwable $exception): \Throwable
    {
        return match (true) {
            $exception instanceof NotFoundException => 
                new ItemNotFoundException($exception->getMessage()),
            $exception instanceof ValidationException => 
                new \InvalidArgumentException($exception->getMessage(), 422),
            $exception instanceof AccessDeniedException => 
                new AccessDeniedHttpException($exception->getMessage()),
            default => $exception,
        };
    }
}
```

## Documentation Patterns

### OpenAPI Annotations
```php
#[ApiResource(
    operations: [
        new Post(
            openapi: new Model\Operation(
                summary: 'Create a new {entity}',
                description: 'Creates a new {entity} with the provided data',
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/{Entity}Input',
                            ],
                            'example' => [
                                'title' => 'Example Title',
                                'content' => 'Example content',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: '{Entity} created successfully',
                    ),
                    '422' => new Model\Response(
                        description: 'Validation error',
                    ),
                ],
            ),
        ),
    ],
)]
```