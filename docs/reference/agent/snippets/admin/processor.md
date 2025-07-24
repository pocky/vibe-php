# Admin Processor Templates

## Create Processor Template

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Web\Admin\Processor;

use App\[Context]Context\Application\Gateway\Create[Resource]\Gateway as Create[Resource]Gateway;
use App\[Context]Context\Application\Gateway\Create[Resource]\Request as Create[Resource]Request;
use App\[Context]Context\Domain\Create[Resource]\Exception\[Resource]AlreadyExists;
use App\[Context]Context\UI\Web\Admin\Resource\[Resource]Resource;
use App\Shared\Application\Gateway\GatewayException;
use Sylius\Component\Resource\Context\Context;
use Sylius\Component\Resource\Metadata\Operation;
use Sylius\Component\Resource\State\ProcessorInterface;

final readonly class Create[Resource]Processor implements ProcessorInterface
{
    public function __construct(
        private Create[Resource]Gateway $create[Resource]Gateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var [Resource]Resource $data */
        if (!$data instanceof [Resource]Resource) {
            throw new \InvalidArgumentException('Expected [Resource]Resource');
        }

        try {
            $gatewayRequest = Create[Resource]Request::fromData([
                'name' => $data->name,
                'description' => $data->description,
                'slug' => $data->slug,
                'status' => $data->status ?? 'active',
                'createdAt' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
            ]);

            $gatewayResponse = ($this->create[Resource]Gateway)($gatewayRequest);
            $responseData = $gatewayResponse->data();

            // Return updated resource with generated data
            return new [Resource]Resource(
                id: $responseData['[resource]Id'],
                name: $data->name,
                description: $data->description,
                slug: $responseData['slug'] ?? $data->slug,
                status: $responseData['status'] ?? $data->status,
                createdAt: new \DateTimeImmutable(),
                updatedAt: new \DateTimeImmutable(),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch ([Resource]AlreadyExists $e) {
            throw new \RuntimeException('[Resource] with this slug already exists', 409, $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                throw new \RuntimeException('[Resource] with this slug already exists', 409, $e);
            }
            throw $e;
        }
    }
}
```

## Update Processor Template

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Web\Admin\Processor;

use App\[Context]Context\Application\Gateway\Update[Resource]\Gateway as Update[Resource]Gateway;
use App\[Context]Context\Application\Gateway\Update[Resource]\Request as Update[Resource]Request;
use App\[Context]Context\Domain\Update[Resource]\Exception\[Resource]NotFound;
use App\[Context]Context\UI\Web\Admin\Resource\[Resource]Resource;
use App\Shared\Application\Gateway\GatewayException;
use Sylius\Component\Resource\Context\Context;
use Sylius\Component\Resource\Metadata\Operation;
use Sylius\Component\Resource\State\ProcessorInterface;

final readonly class Update[Resource]Processor implements ProcessorInterface
{
    public function __construct(
        private Update[Resource]Gateway $update[Resource]Gateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var [Resource]Resource $data */
        if (!$data instanceof [Resource]Resource) {
            throw new \InvalidArgumentException('Expected [Resource]Resource');
        }

        try {
            $gatewayRequest = Update[Resource]Request::fromData([
                '[resource]Id' => $data->id,
                'name' => $data->name,
                'description' => $data->description,
                'slug' => $data->slug,
                'status' => $data->status,
                'updatedAt' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
            ]);

            $gatewayResponse = ($this->update[Resource]Gateway)($gatewayRequest);
            $responseData = $gatewayResponse->data();

            // Return updated resource
            return new [Resource]Resource(
                id: $data->id,
                name: $responseData['name'] ?? $data->name,
                description: $responseData['description'] ?? $data->description,
                slug: $responseData['slug'] ?? $data->slug,
                status: $responseData['status'] ?? $data->status,
                createdAt: $data->createdAt,
                updatedAt: new \DateTimeImmutable(),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch ([Resource]NotFound $e) {
            throw new \RuntimeException('[Resource] not found', 404, $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('[Resource] not found', 404, $e);
            }
            throw $e;
        }
    }
}
```

## Delete Processor Template

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Web\Admin\Processor;

use App\[Context]Context\Application\Gateway\Delete[Resource]\Gateway as Delete[Resource]Gateway;
use App\[Context]Context\Application\Gateway\Delete[Resource]\Request as Delete[Resource]Request;
use App\[Context]Context\Domain\Delete[Resource]\Exception\[Resource]NotFound;
use App\[Context]Context\UI\Web\Admin\Resource\[Resource]Resource;
use App\Shared\Application\Gateway\GatewayException;
use Sylius\Component\Resource\Context\Context;
use Sylius\Component\Resource\Metadata\Operation;
use Sylius\Component\Resource\State\ProcessorInterface;

final readonly class Delete[Resource]Processor implements ProcessorInterface
{
    public function __construct(
        private Delete[Resource]Gateway $delete[Resource]Gateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var [Resource]Resource $data */
        if (!$data instanceof [Resource]Resource) {
            throw new \InvalidArgumentException('Expected [Resource]Resource');
        }

        try {
            $gatewayRequest = Delete[Resource]Request::fromData([
                '[resource]Id' => $data->id,
            ]);

            ($this->delete[Resource]Gateway)($gatewayRequest);

            // Return null for successful deletion
            return null;
        } catch ([Resource]NotFound $e) {
            throw new \RuntimeException('[Resource] not found', 404, $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('[Resource] not found', 404, $e);
            }
            if (str_contains($e->getMessage(), 'has children') || str_contains($e->getMessage(), 'in use')) {
                throw new \RuntimeException('[Resource] cannot be deleted because it is in use', 409, $e);
            }
            throw $e;
        }
    }
}
```

## Custom Action Processor Template

```php
<?php

declare(strict_types=1);

namespace App\[Context]Context\UI\Web\Admin\Processor;

use App\[Context]Context\Application\Gateway\[Action][Resource]\Gateway as [Action][Resource]Gateway;
use App\[Context]Context\Application\Gateway\[Action][Resource]\Request as [Action][Resource]Request;
use App\[Context]Context\UI\Web\Admin\Resource\[Resource]Resource;
use Sylius\Component\Resource\Context\Context;
use Sylius\Component\Resource\Metadata\Operation;
use Sylius\Component\Resource\State\ProcessorInterface;

final readonly class [Action][Resource]Processor implements ProcessorInterface
{
    public function __construct(
        private [Action][Resource]Gateway $[action][Resource]Gateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var [Resource]Resource $data */
        $formName = $context->getContext()['form_name'] ?? null;
        
        if ($formName !== '[action]') {
            throw new \RuntimeException('Invalid form name for [action] action');
        }

        try {
            $gatewayRequest = [Action][Resource]Request::fromData([
                '[resource]Id' => $data->id,
                'actionBy' => 'admin', // Or get from security context
                'actionAt' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
                // Add any additional data from form
            ]);

            $gatewayResponse = ($this->[action][Resource]Gateway)($gatewayRequest);
            
            // Update resource with action results
            $data->status = '[actioned]';
            
            return $data;
        } catch (\Exception $e) {
            throw new \RuntimeException('[Action] failed: ' . $e->getMessage(), 500, $e);
        }
    }
}
```

## Batch Processor Template

```php
// For bulk operations
public function process(mixed $data, Operation $operation, Context $context): mixed
{
    $ids = $context->getContext()['ids'] ?? [];
    $action = $context->getContext()['action'] ?? null;
    
    if (empty($ids) || null === $action) {
        throw new \InvalidArgumentException('No items selected for batch operation');
    }
    
    $results = [
        'success' => 0,
        'failed' => 0,
        'errors' => [],
    ];
    
    foreach ($ids as $id) {
        try {
            $request = match ($action) {
                'publish' => Publish[Resource]Request::fromData(['[resource]Id' => $id]),
                'archive' => Archive[Resource]Request::fromData(['[resource]Id' => $id]),
                'delete' => Delete[Resource]Request::fromData(['[resource]Id' => $id]),
                default => throw new \InvalidArgumentException("Unknown action: {$action}"),
            };
            
            ($this->gateway)($request);
            $results['success']++;
        } catch (\Exception $e) {
            $results['failed']++;
            $results['errors'][$id] = $e->getMessage();
        }
    }
    
    if ($results['failed'] > 0) {
        throw new \RuntimeException(sprintf(
            'Batch operation partially failed: %d succeeded, %d failed',
            $results['success'],
            $results['failed']
        ));
    }
    
    return null;
}
```