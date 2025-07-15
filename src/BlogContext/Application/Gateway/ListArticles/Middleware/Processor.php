<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ListArticles\Middleware;

use App\BlogContext\Application\Gateway\ListArticles\Request;
use App\BlogContext\Application\Gateway\ListArticles\Response;
use App\BlogContext\Application\Operation\Query\ListArticles\Handler;
use App\BlogContext\Application\Operation\Query\ListArticles\Query;
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
        $query = new Query(
            page: $request->page,
            limit: $request->limit,
            status: $request->status,
            search: $request->search,
            articleId: $request->articleId,
        );

        $result = ($this->handler)($query);

        return new Response(
            articles: $result['articles'],
            total: $result['total'],
            page: $result['page'],
            limit: $result['limit'],
            hasNextPage: $result['hasNextPage'],
        );
    }
}
