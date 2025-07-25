<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetCategoryTree\Middleware;

use App\BlogContext\Application\Gateway\GetCategoryTree\Request;
use App\BlogContext\Application\Gateway\GetCategoryTree\Response;
use App\BlogContext\Application\Operation\Query\GetCategoryTree\CategoryNode;
use App\BlogContext\Application\Operation\Query\GetCategoryTree\HandlerInterface;
use App\BlogContext\Application\Operation\Query\GetCategoryTree\Query;
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
        $query = new Query(
            rootId: $request->rootId,
            maxDepth: $request->maxDepth,
        );

        $view = ($this->handler)($query);

        return new Response(
            tree: $this->transformNodes($view->tree),
        );
    }

    /**
     * @param array<CategoryNode> $nodes
     *
     * @return array<array{
     *     id: string,
     *     name: string,
     *     slug: string,
     *     description: string|null,
     *     order: int,
     *     children: array
     * }>
     */
    private function transformNodes(array $nodes): array
    {
        return array_map(
            fn (CategoryNode $node) => [
                'id' => $node->id,
                'name' => $node->name,
                'slug' => $node->slug,
                'description' => $node->description,
                'order' => $node->order,
                'children' => $this->transformNodes($node->children),
            ],
            $nodes,
        );
    }
}
