# API Platform Resource Template

## Resource Structure

### API Resource Class

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Api\Rest\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\[Context]Context\UI\Api\Rest\Provider\Get[Entity]Provider;
use App\[Context]Context\UI\Api\Rest\Provider\List[Entity]sProvider;
use App\[Context]Context\UI\Api\Rest\Processor\Create[Entity]Processor;
use App\[Context]Context\UI\Api\Rest\Processor\Update[Entity]Processor;
use App\[Context]Context\UI\Api\Rest\Processor\Delete[Entity]Processor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: '[Entity]',
    operations: [
        new Get(
            uriTemplate: '/[entities]/{id}',
            provider: Get[Entity]Provider::class,
        ),
        new GetCollection(
            uriTemplate: '/[entities]',
            provider: List[Entity]sProvider::class,
        ),
        new Post(
            uriTemplate: '/[entities]',
            processor: Create[Entity]Processor::class,
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Put(
            uriTemplate: '/[entities]/{id}',
            provider: Get[Entity]Provider::class,
            processor: Update[Entity]Processor::class,
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Delete(
            uriTemplate: '/[entities]/{id}',
            processor: Delete[Entity]Processor::class,
            security: "is_granted('ROLE_ADMIN')",
        ),
    ],
)]
final class [Entity]Resource
{
    public function __construct(
        #[Assert\Uuid]
        public ?string $id = null,
        
        #[Assert\NotBlank(groups: ['create'])]
        #[Assert\Length(min: 2, max: 100)]
        public ?string $name = null,
        
        #[Assert\Length(max: 500)]
        public ?string $description = null,
        
        #[Assert\Choice(choices: ['draft', 'active', 'archived'])]
        public ?string $status = null,
        
        public ?\DateTimeImmutable $createdAt = null,
        
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
```

### Get Provider (Single Resource)

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Api\Rest\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\[Context]Context\Application\Gateway\Get[Entity]\Gateway as Get[Entity]Gateway;
use App\[Context]Context\Application\Gateway\Get[Entity]\Request as Get[Entity]Request;
use App\[Context]Context\UI\Api\Rest\Resource\[Entity]Resource;
use App\Shared\Application\Gateway\GatewayException;

final readonly class Get[Entity]Provider implements ProviderInterface
{
    public function __construct(
        private Get[Entity]Gateway $get[Entity]Gateway,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['id'])) {
            return null;
        }

        try {
            $request = Get[Entity]Request::fromData(['id' => $uriVariables['id']]);
            $response = ($this->get[Entity]Gateway)($request);
            
            $data = $response->data();
            return isset($data['[entity]']) ? $this->transformToResource($data['[entity]']) : null;
        } catch (GatewayException | \RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return null; // API Platform will return 404
            }
            throw $e;
        }
    }
    
    private function transformToResource(array $data): [Entity]Resource
    {
        return new [Entity]Resource(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            status: $data['status'],
            createdAt: isset($data['createdAt']) ? new \DateTimeImmutable($data['createdAt']) : null,
            updatedAt: isset($data['updatedAt']) ? new \DateTimeImmutable($data['updatedAt']) : null,
        );
    }
}
```

### List Provider (Collection)

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Api\Rest\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\[Context]Context\Application\Gateway\List[Entity]s\Gateway as List[Entity]sGateway;
use App\[Context]Context\Application\Gateway\List[Entity]s\Request as List[Entity]sRequest;
use App\[Context]Context\UI\Api\Rest\Resource\[Entity]Resource;

final readonly class List[Entity]sProvider implements ProviderInterface
{
    public function __construct(
        private List[Entity]sGateway $list[Entity]sGateway,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $filters = $context['filters'] ?? [];
        
        $request = List[Entity]sRequest::fromData([
            'page' => (int) ($filters['page'] ?? 1),
            'limit' => (int) ($filters['itemsPerPage'] ?? 20),
            'status' => $filters['status'] ?? null,
            'search' => $filters['search'] ?? null,
        ]);
        
        $response = ($this->list[Entity]sGateway)($request);
        
        $data = $response->data();
        $[entities] = $data['[entities]'] ?? [];
        
        return array_map(
            fn (array $[entity]) => $this->transformToResource($[entity]),
            $[entities]
        );
    }
    
    private function transformToResource(array $data): [Entity]Resource
    {
        return new [Entity]Resource(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            status: $data['status'],
            createdAt: isset($data['createdAt']) ? new \DateTimeImmutable($data['createdAt']) : null,
            updatedAt: isset($data['updatedAt']) ? new \DateTimeImmutable($data['updatedAt']) : null,
        );
    }
}
```

### Create Processor

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\[Context]Context\Application\Gateway\Create[Entity]\Gateway as Create[Entity]Gateway;
use App\[Context]Context\Application\Gateway\Create[Entity]\Request as Create[Entity]Request;
use App\[Context]Context\UI\Api\Rest\Resource\[Entity]Resource;
use App\Shared\Application\Gateway\GatewayException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final readonly class Create[Entity]Processor implements ProcessorInterface
{
    public function __construct(
        private Create[Entity]Gateway $create[Entity]Gateway,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var [Entity]Resource $data */
        try {
            $request = Create[Entity]Request::fromData([
                'name' => $data->name,
                'description' => $data->description,
            ]);

            $response = ($this->create[Entity]Gateway)($request);
            $responseData = $response->data();

            return new [Entity]Resource(
                id: $responseData['id'],
                name: $data->name,
                description: $data->description,
                status: $responseData['status'],
                createdAt: new \DateTimeImmutable($responseData['createdAt']),
                updatedAt: new \DateTimeImmutable($responseData['updatedAt']),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (GatewayException | \RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                throw new ConflictHttpException('[Entity] with this name already exists', $e);
            }
            throw $e;
        }
    }
}
```

### Update Processor

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\[Context]Context\Application\Gateway\Update[Entity]\Gateway as Update[Entity]Gateway;
use App\[Context]Context\Application\Gateway\Update[Entity]\Request as Update[Entity]Request;
use App\[Context]Context\UI\Api\Rest\Resource\[Entity]Resource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class Update[Entity]Processor implements ProcessorInterface
{
    public function __construct(
        private Update[Entity]Gateway $update[Entity]Gateway,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var [Entity]Resource $data */
        try {
            $request = Update[Entity]Request::fromData([
                'id' => $uriVariables['id'],
                'name' => $data->name,
                'description' => $data->description,
                'status' => $data->status,
            ]);

            $response = ($this->update[Entity]Gateway)($request);
            $responseData = $response->data();

            return new [Entity]Resource(
                id: $responseData['id'],
                name: $responseData['name'],
                description: $responseData['description'],
                status: $responseData['status'],
                createdAt: $data->createdAt,
                updatedAt: new \DateTimeImmutable($responseData['updatedAt']),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException('[Entity] not found', $e);
            }
            throw $e;
        }
    }
}
```

### Delete Processor

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\[Context]Context\Application\Gateway\Delete[Entity]\Gateway as Delete[Entity]Gateway;
use App\[Context]Context\Application\Gateway\Delete[Entity]\Request as Delete[Entity]Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class Delete[Entity]Processor implements ProcessorInterface
{
    public function __construct(
        private Delete[Entity]Gateway $delete[Entity]Gateway,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        try {
            $request = Delete[Entity]Request::fromData([
                'id' => $uriVariables['id'],
            ]);

            ($this->delete[Entity]Gateway)($request);

            return null; // 204 No Content
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException('[Entity] not found', $e);
            }
            throw $e;
        }
    }
}
```

## Search Filter (Optional)

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Api\Rest\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

final class [Entity]SearchFilter extends AbstractFilter
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
                ->andWhere("{$alias}.name LIKE :search OR {$alias}.description LIKE :search")
                ->setParameter('search', "%{$value}%");
        }
        
        if ($property === 'status') {
            $alias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->andWhere("{$alias}.status = :status")
                ->setParameter('status', $value);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'search' => [
                'property' => 'search',
                'type' => 'string',
                'required' => false,
                'description' => 'Search in name and description',
            ],
            'status' => [
                'property' => 'status',
                'type' => 'string',
                'required' => false,
                'description' => 'Filter by status',
                'enum' => ['draft', 'active', 'archived'],
            ],
        ];
    }
}
```