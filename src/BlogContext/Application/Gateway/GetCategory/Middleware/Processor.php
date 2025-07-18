<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetCategory\Middleware;

use App\BlogContext\Application\Gateway\GetCategory\Request;
use App\BlogContext\Application\Gateway\GetCategory\Response;
use App\BlogContext\Application\Operation\Query\GetCategory\Handler;
use App\BlogContext\Application\Operation\Query\GetCategory\Query;
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
            id: $request->id,
        );

        // Execute query through handler
        $view = ($this->handler)($query);

        // Return response with entity data
        return new Response(
            category: $view->toArray(),
        );
    }
}
