<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ListCategories\Middleware;

use App\BlogContext\Application\Gateway\ListCategories\Request;
use App\BlogContext\Application\Gateway\ListCategories\Response;
use App\BlogContext\Application\Operation\Query\ListCategories\Handler;
use App\BlogContext\Application\Operation\Query\ListCategories\Query;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */

        // Create query
        $query = new Query(
            page: $request->page,
            limit: $request->limit,
            sortBy: $request->sortBy,
            sortOrder: $request->sortOrder,
        );

        // Execute query through handler
        $view = ($this->handler)($query);

        // Return response with collection data
        return new Response(
            categories: $view->toArray()['categories'],
            total: $view->total,
            page: $request->page,
            limit: $request->limit,
        );
    }
}
