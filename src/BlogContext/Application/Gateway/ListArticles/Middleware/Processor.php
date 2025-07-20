<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ListArticles\Middleware;

use App\BlogContext\Application\Gateway\ListArticles\Request;
use App\BlogContext\Application\Gateway\ListArticles\Response;
use App\BlogContext\Application\Operation\Query\ListArticles\HandlerInterface;
use App\BlogContext\Application\Operation\Query\ListArticles\Query;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private HandlerInterface $handler,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */

        // Create query
        $query = new Query(
            page: $request->page,
            limit: $request->limit,
            sortBy: $request->sortBy,
            sortOrder: $request->sortOrder,
            status: $request->status,
            authorId: $request->authorId,
        );

        // Execute query through handler
        $result = ($this->handler)($query);

        // Transform Article objects to arrays
        $articles = array_map(
            fn ($article) => [
                'id' => $article->id->getValue(),
                'title' => $article->title->getValue(),
                'content' => $article->excerpt ?? '',  // Use excerpt for list view
                'slug' => $article->slug->getValue(),
                'status' => $article->status->value,
                'authorId' => $article->authorId,
                'createdAt' => $article->timestamps->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'updatedAt' => $article->timestamps->getUpdatedAt()->format(\DateTimeInterface::ATOM),
                'publishedAt' => $article->publishedAt?->format(\DateTimeInterface::ATOM),
            ],
            $result->articles
        );

        // Return response with collection data
        return new Response(
            articles: $articles,
            total: $result->total,
            page: $result->page,
            limit: $result->limit,
            totalPages: $result->totalPages,
        );
    }
}
