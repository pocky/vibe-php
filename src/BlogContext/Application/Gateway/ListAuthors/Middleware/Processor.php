<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ListAuthors\Middleware;

use App\BlogContext\Application\Gateway\ListAuthors\Request;
use App\BlogContext\Application\Gateway\ListAuthors\Response;
use App\BlogContext\Application\Operation\Query\ListAuthors\Handler;
use App\BlogContext\Application\Operation\Query\ListAuthors\Query;
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
            page: $request->page ?? 1,
            limit: $request->limit ?? 20,
        );

        // Execute query through handler
        $view = ($this->handler)($query);

        // Return response with collection data
        return new Response(
            authors: array_map(
                fn (array $author) => [
                    'id' => $author['id'],
                    'name' => $author['name'],
                    'email' => $author['email'],
                    'bio' => $author['bio'],
                    'createdAt' => $author['createdAt'],
                    'updatedAt' => $author['updatedAt'],
                ],
                $view->authors
            ),
            total: $view->total,
            page: $view->page,
            limit: $view->limit,
        );
    }
}
