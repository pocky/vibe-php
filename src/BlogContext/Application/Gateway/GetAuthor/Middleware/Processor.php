<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetAuthor\Middleware;

use App\BlogContext\Application\Gateway\GetAuthor\Request;
use App\BlogContext\Application\Gateway\GetAuthor\Response;
use App\BlogContext\Application\Operation\Query\GetAuthor\Handler;
use App\BlogContext\Application\Operation\Query\GetAuthor\Query;
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
