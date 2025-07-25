<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetCategory\Middleware;

use App\BlogContext\Application\Gateway\GetCategory\Request;
use App\BlogContext\Application\Gateway\GetCategory\Response;
use App\BlogContext\Application\Operation\Query\GetCategory\HandlerInterface;
use App\BlogContext\Application\Operation\Query\GetCategory\Query;
use App\BlogContext\Domain\GetCategory\Exception\CategoryNotFound;
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
        try {
            $query = new Query(
                id: $request->categoryId,
            );

            $view = ($this->handler)($query);

            return new Response(
                id: $view->id,
                name: $view->name,
                slug: $view->slug,
                description: $view->description,
                parentId: $view->parentId,
                order: $view->order,
                createdAt: $view->createdAt,
                updatedAt: $view->updatedAt,
            );
        } catch (CategoryNotFound $e) {
            throw new \RuntimeException($e->getMessage(), 404, $e);
        }
    }
}
