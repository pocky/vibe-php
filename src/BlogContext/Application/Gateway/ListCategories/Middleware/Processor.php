<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ListCategories\Middleware;

use App\BlogContext\Application\Gateway\ListCategories\Request;
use App\BlogContext\Application\Gateway\ListCategories\Response;
use App\BlogContext\Application\Operation\Query\ListCategories\HandlerInterface;
use App\BlogContext\Application\Operation\Query\ListCategories\Query;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private HandlerInterface $handler,
    ) {
    }

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */
        $query = new Query(
            page: $request->page,
            limit: $request->limit,
            sortBy: $request->sortBy,
            sortOrder: $request->sortOrder,
            parentId: $request->parentId,
        );

        $view = ($this->handler)($query);

        return new Response(
            categories: array_map(
                fn ($category) => [
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
