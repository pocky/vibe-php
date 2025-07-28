<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Category\ListCategories\Middleware;

use App\Blog\Application\Gateway\Category\ListCategories\Request;
use App\Blog\Application\Gateway\Category\ListCategories\Response;
use App\Blog\Application\Operation\Query\Category\ListCategories\Handler;
use App\Blog\Application\Operation\Query\Category\ListCategories\Query;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public function __invoke(GatewayRequest $gatewayRequest): GatewayResponse
    {
        /** @var Request $gatewayRequest */

        $query = new Query(
            page: $gatewayRequest->page,
            limit: $gatewayRequest->limit,
            sortBy: $gatewayRequest->sortBy,
            sortOrder: $gatewayRequest->sortOrder,
            parentId: $gatewayRequest->parentId,
        );

        $view = ($this->handler)($query);

        return new Response(
            categories: array_map(
                fn ($category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'parentId' => $category->parentId,
                    'order' => $category->order,
                    'createdAt' => $category->createdAt,
                    'updatedAt' => $category->updatedAt,
                ],
                $view->categories,
            ),
            total: $view->total,
            page: $view->page,
            limit: $view->limit,
        );
    }
}
