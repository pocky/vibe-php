# API Resource Snippets

## Resource Class

```php
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\{Get, GetCollection, Post, Put, Delete};

#[ApiResource(
    shortName: '[Entity]',
    operations: [
        new Get('/[entities]/{id}', provider: Get[Entity]Provider::class),
        new GetCollection('/[entities]', provider: List[Entity]sProvider::class),
        new Post('/[entities]', processor: Create[Entity]Processor::class),
        new Put('/[entities]/{id}', processor: Update[Entity]Processor::class),
        new Delete('/[entities]/{id}', processor: Delete[Entity]Processor::class),
    ],
)]
final class [Entity]Resource
{
    #[Groups(['[entity]:read'])]
    public \DateTimeImmutable|null $createdAt = null;

    #[Groups(['[entity]:read'])]
    public \DateTimeImmutable|null $updatedAt = null;

    public function __construct(
        #[Groups(['[entity]:read'])]
        #[Assert\Uuid]
        public string|null $id = null,
        
        #[Groups(['[entity]:read', '[entity]:create', '[entity]:update'])]
        #[Assert\NotBlank(groups: ['[entity]:create', '[entity]:update'])]
        #[Assert\Length(min: 2, max: 100, groups: ['[entity]:create', '[entity]:update'])]
        public string|null $name = null,
        
        #[Groups(['[entity]:read'])]
        public string|null $status = null,
        
        \DateTimeInterface|string|null $createdAt = null,
        \DateTimeInterface|string|null $updatedAt = null,
    ) {
        // Handle DateTime conversion from strings
        if (is_string($createdAt)) {
            try {
                $this->createdAt = new \DateTimeImmutable($createdAt);
            } catch (\Exception) {
                $this->createdAt = null;
            }
        } else {
            $this->createdAt = $createdAt instanceof \DateTimeImmutable
                ? $createdAt
                : ($createdAt instanceof \DateTime ? \DateTimeImmutable::createFromMutable($createdAt) : null);
        }

        if (is_string($updatedAt)) {
            try {
                $this->updatedAt = new \DateTimeImmutable($updatedAt);
            } catch (\Exception) {
                $this->updatedAt = null;
            }
        } else {
            $this->updatedAt = $updatedAt instanceof \DateTimeImmutable
                ? $updatedAt
                : ($updatedAt instanceof \DateTime ? \DateTimeImmutable::createFromMutable($updatedAt) : null);
        }
    }
}
```

## Get Provider

```php
final readonly class Get[Entity]Provider implements ProviderInterface
{
    public function __construct(private Get[Entity]Gateway $gateway) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        try {
            $request = Get[Entity]Request::fromData(['id' => $uriVariables['id']]);
            $response = ($this->gateway)($request);
            
            return $this->transformToResource($response->data()['[entity]']);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return null; // 404
            }
            throw $e;
        }
    }
    
    private function transformToResource(array $data): [Entity]Resource
    {
        return new [Entity]Resource(
            id: $data['id'],
            name: $data['name'],
            status: $data['status'],
            createdAt: new \DateTimeImmutable($data['createdAt']),
        );
    }
}
```

## List Provider

```php
final readonly class List[Entity]sProvider implements ProviderInterface
{
    public function __construct(private List[Entity]sGateway $gateway) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $filters = $context['filters'] ?? [];
        
        $request = List[Entity]sRequest::fromData([
            'page' => (int) ($filters['page'] ?? 1),
            'limit' => (int) ($filters['itemsPerPage'] ?? 20),
            'status' => $filters['status'] ?? null,
        ]);
        
        $response = ($this->gateway)($request);
        $[entities] = $response->data()['[entities]'] ?? [];
        
        return array_map(
            fn (array $data) => $this->transformToResource($data),
            $[entities]
        );
    }
}
```

## Create Processor

```php
final readonly class Create[Entity]Processor implements ProcessorInterface
{
    public function __construct(private Create[Entity]Gateway $gateway) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        try {
            $request = Create[Entity]Request::fromData([
                'name' => $data->name,
                'status' => $data->status ?? 'draft',
            ]);

            $response = ($this->gateway)($request);
            $responseData = $response->data();

            return new [Entity]Resource(
                id: $responseData['id'],
                name: $data->name,
                status: $responseData['status'],
                createdAt: new \DateTimeImmutable(),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        }
    }
}
```

## Update/Delete Processors

```php
// Update
public function process(...): mixed
{
    $request = Update[Entity]Request::fromData([
        'id' => $uriVariables['id'],
        'name' => $data->name,
        'status' => $data->status,
    ]);
    
    $response = ($this->gateway)($request);
    return $this->transformToResource($response->data());
}

// Delete
public function process(...): mixed
{
    $request = Delete[Entity]Request::fromData(['id' => $uriVariables['id']]);
    ($this->gateway)($request);
    return null; // 204
}
```