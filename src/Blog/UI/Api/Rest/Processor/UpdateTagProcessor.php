<?php

declare(strict_types=1);

namespace App\Blog\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Blog\Application\Gateway\Tag\UpdateTag\Gateway as UpdateTagGateway;
use App\Blog\Application\Gateway\Tag\UpdateTag\Request as UpdateTagRequest;
use App\Blog\UI\Api\Rest\Resource\TagResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<TagResource, TagResource>
 */
final readonly class UpdateTagProcessor implements ProcessorInterface
{
    public function __construct(
        private UpdateTagGateway $updateTagGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TagResource
    {
        assert($data instanceof TagResource);

        $id = $uriVariables['id'] ?? null;
        if (!$id) {
            throw new NotFoundHttpException('Tag not found');
        }

        $request = new UpdateTagRequest(
            id: $id,
            name: $data->name,
            slug: $data->slug,
        );

        try {
            $response = ($this->updateTagGateway)($request);
        } catch (\Exception $exception) {
            if (str_contains($exception->getMessage(), 'not found')) {
                throw new NotFoundHttpException('Tag not found', $exception);
            }

            throw new UnprocessableEntityHttpException($exception->getMessage(), $exception);
        }

        return new TagResource(
            id: $response->id,
            name: $response->name,
            slug: $response->slug,
            createdAt: $data->createdAt,
            updatedAt: $response->updatedAt,
        );
    }
}
