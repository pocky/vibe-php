<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetAuthorArticles\Middleware;

use App\BlogContext\Application\Gateway\GetAuthorArticles\Request;
use App\BlogContext\Application\Gateway\GetAuthorArticles\Response;
use App\BlogContext\Application\Operation\Query\GetAuthorArticles\Handler;
use App\BlogContext\Application\Operation\Query\GetAuthorArticles\Query;
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
            authorId: $request->authorId,
            page: $request->page ?? 1,
            limit: $request->limit ?? 20,
        );

        // Execute query through handler
        $view = ($this->handler)($query);

        // Return response with articles data
        return new Response(
            articles: $view->articles,
            total: $view->total,
            page: $view->page,
            limit: $view->limit,
        );
    }
}
