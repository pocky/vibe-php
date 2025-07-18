<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BlogContext\Application\Gateway\CreateCategory\Gateway as CreateCategoryGateway;
use App\BlogContext\Application\Gateway\CreateCategory\Request as CreateCategoryRequest;
use App\BlogContext\Domain\CreateCategory\Exception\CategoryAlreadyExists;
use App\BlogContext\UI\Api\Rest\Resource\CategoryResource;
use App\Shared\Application\Gateway\GatewayException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final readonly class CreateCategoryProcessor implements ProcessorInterface
{
    public function __construct(
        private CreateCategoryGateway $createCategoryGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var CategoryResource $data */
        try {
            $request = CreateCategoryRequest::fromData([
                // TODO: Map resource data to gateway request
                // 'title' => $data->title,
                // 'content' => $data->content,
                // 'status' => $data->status ?? 'draft',
                'createdAt' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
            ]);

            $response = ($this->createCategoryGateway)($request);
            $responseData = $response->data();

            return new CategoryResource(
                id: $responseData['categoryId'],
                // TODO: Map response data back to resource
                // title: $data->title,
                // content: $data->content,
                createdAt: new \DateTimeImmutable(),
                updatedAt: new \DateTimeImmutable(),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (CategoryAlreadyExists|GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                throw new ConflictHttpException('Category already exists', $e);
            }

            throw $e;
        }
    }
}
