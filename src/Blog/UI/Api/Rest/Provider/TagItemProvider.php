<?php

declare(strict_types=1);

namespace App\Blog\UI\Api\Rest\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Blog\Application\Gateway\Tag\GetTag\Gateway as GetTagGateway;
use App\Blog\Application\Gateway\Tag\GetTag\Request as GetTagRequest;
use App\Blog\UI\Api\Rest\Resource\TagResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<TagResource>
 */
final readonly class TagItemProvider implements ProviderInterface
{
    public function __construct(
        private GetTagGateway $getTagGateway,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TagResource|null
    {
        $id = $uriVariables['id'] ?? null;
        if (!$id) {
            throw new NotFoundHttpException('Tag not found');
        }

        try {
            $response = ($this->getTagGateway)(new GetTagRequest($id));
        } catch (\Exception) {
            throw new NotFoundHttpException('Tag not found');
        }

        $tag = $response->tag;

        return new TagResource(
            id: $tag['id'],
            name: $tag['name'],
            slug: $tag['slug'],
            createdAt: $tag['createdAt'],
            updatedAt: $tag['updatedAt'],
        );
    }
}
