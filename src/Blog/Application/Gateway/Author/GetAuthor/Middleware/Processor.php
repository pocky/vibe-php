<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Author\GetAuthor\Middleware;

use App\Blog\Application\Gateway\Author\GetAuthor\Request;
use App\Blog\Application\Gateway\Author\GetAuthor\Response;
use App\Blog\Application\Operation\Query\Author\GetAuthor\Handler;
use App\Blog\Application\Operation\Query\Author\GetAuthor\Query;
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
        );

        // Execute query through handler
        $view = ($this->handler)($query);

        // Return response with author data
        return new Response(
            author: [
                'id' => $view->id,
                'name' => $view->name,
                'email' => $view->email,
                'bio' => $view->bio,
                'createdAt' => $view->createdAt,
                'updatedAt' => $view->updatedAt,
            ],
        );
    }
}
