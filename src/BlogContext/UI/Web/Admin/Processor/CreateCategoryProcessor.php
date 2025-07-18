<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Processor;

use App\BlogContext\Application\Gateway\CreateCategory\Gateway as CreateCategoryGateway;
use App\BlogContext\Application\Gateway\CreateCategory\Request as CreateCategoryRequest;
use App\BlogContext\Domain\CreateCategory\Exception\CategoryAlreadyExists;
use App\BlogContext\UI\Web\Admin\Resource\CategoryResource;
use App\Shared\Application\Gateway\GatewayException;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;

final readonly class CreateCategoryProcessor implements ProcessorInterface
{
    public function __construct(
        private CreateCategoryGateway $createCategoryGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var CategoryResource $data */
        if (!$data instanceof CategoryResource) {
            throw new \InvalidArgumentException('Expected CategoryResource');
        }

        try {
            $gatewayRequest = CreateCategoryRequest::fromData([
                'name' => $data->name ?? '',
                'slug' => $data->slug ?? '',
                'parentId' => $data->parentId,
            ]);

            $gatewayResponse = ($this->createCategoryGateway)($gatewayRequest);
            $responseData = $gatewayResponse->data();

            // Return updated resource with generated data
            return new CategoryResource(
                id: $responseData['categoryId'] ?? null,
                name: $data->name,
                slug: $responseData['slug'] ?? $data->slug,
                path: $responseData['path'] ?? null,
                parentId: $data->parentId,
                level: (int) ($responseData['level'] ?? 1),
                articleCount: 0,
                description: $data->description,
                createdAt: new \DateTimeImmutable(),
                updatedAt: new \DateTimeImmutable(),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (CategoryAlreadyExists $e) {
            throw new \RuntimeException('Category already exists', 409, $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                throw new \RuntimeException('Category already exists', 409, $e);
            }

            throw $e;
        }
    }
}
