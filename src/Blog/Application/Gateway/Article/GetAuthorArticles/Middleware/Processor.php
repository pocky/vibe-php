<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Article\GetAuthorArticles\Middleware;

use App\Blog\Application\Gateway\Article\GetAuthorArticles\Request;
use App\Blog\Application\Gateway\Article\GetAuthorArticles\Response;
use App\Blog\Application\Operation\Query\Article\GetAuthorArticles\Handler;
use App\Blog\Application\Operation\Query\Article\GetAuthorArticles\Query;
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

        // Create query
        $query = new Query(
            authorId: $gatewayRequest->authorId,
            page: $gatewayRequest->page ?? 1,
            limit: $gatewayRequest->limit ?? 20,
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
