<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Author\ListAuthors\Middleware;

use App\Blog\Application\Gateway\Author\ListAuthors\Request;
use App\Blog\Application\Gateway\Author\ListAuthors\Response;
use App\Blog\Application\Operation\Query\Author\ListAuthors\Handler;
use App\Blog\Application\Operation\Query\Author\ListAuthors\Query;
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
            page: $gatewayRequest->page ?? 1,
            limit: $gatewayRequest->limit ?? 20,
        );

        // Execute query through handler
        $view = ($this->handler)($query);

        // Return response with collection data
        return new Response(
            authors: array_map(
                fn (array $author): array => [
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
