<?php

declare(strict_types=1);

namespace App\Blog\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Blog\Application\Gateway\Tag\CreateTag\Gateway as CreateTagGateway;
use App\Blog\Application\Gateway\Tag\CreateTag\Request as CreateTagRequest;
use App\Blog\Domain\Tag\Shared\Exception\TagAlreadyExists;
use App\Blog\UI\Api\Rest\Resource\TagResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<TagResource, TagResource>
 */
final readonly class CreateTagProcessor implements ProcessorInterface
{
    public function __construct(
        private CreateTagGateway $createTagGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TagResource
    {
        assert($data instanceof TagResource);

        $request = new CreateTagRequest(
            name: $data->name,
            slug: $data->slug,
        );

        try {
            $response = ($this->createTagGateway)($request);
        } catch (TagAlreadyExists $tagAlreadyExists) {
            throw new UnprocessableEntityHttpException($tagAlreadyExists->getMessage(), $tagAlreadyExists);
        }

        // Set the response status code to 201 Created
        if (isset($context['response']) && $context['response'] instanceof Response) {
            $context['response']->setStatusCode(Response::HTTP_CREATED);
        }

        return new TagResource(
            id: $response->id,
            name: $response->name,
            slug: $response->slug,
            createdAt: $response->createdAt,
            updatedAt: $response->createdAt,
        );
    }
}
