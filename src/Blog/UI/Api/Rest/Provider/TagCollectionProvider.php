<?php

declare(strict_types=1);

namespace App\Blog\UI\Api\Rest\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Blog\Application\Gateway\Tag\ListTags\Gateway as ListTagsGateway;
use App\Blog\Application\Gateway\Tag\ListTags\Request as ListTagsRequest;
use App\Blog\UI\Api\Rest\Resource\TagResource;

/**
 * @implements ProviderInterface<TagResource>
 */
final readonly class TagCollectionProvider implements ProviderInterface
{
    public function __construct(
        private ListTagsGateway $listTagsGateway,
        private Pagination $pagination,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|object|null
    {
        $page = $this->pagination->getPage($context);
        $itemsPerPage = $this->pagination->getLimit($operation, $context);

        $request = new ListTagsRequest(
            page: $page,
            itemsPerPage: $itemsPerPage,
        );

        $response = ($this->listTagsGateway)($request);

        $resources = [];
        /** @var array{id: string, name: string, slug: string, createdAt: string, updatedAt: string} $tag */
        foreach ($response->tags as $tag) {
            $resources[] = new TagResource(
                id: $tag['id'],
                name: $tag['name'],
                slug: $tag['slug'],
                createdAt: $tag['createdAt'],
                updatedAt: $tag['updatedAt'],
            );
        }

        return new TraversablePaginator(
            new \ArrayIterator($resources),
            $page,
            $itemsPerPage,
            $response->total
        );
    }
}
