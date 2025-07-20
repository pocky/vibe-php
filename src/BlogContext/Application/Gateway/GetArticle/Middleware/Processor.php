<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetArticle\Middleware;

use App\BlogContext\Application\Gateway\GetArticle\Request;
use App\BlogContext\Application\Gateway\GetArticle\Response;
use App\BlogContext\Application\Operation\Query\GetArticle\HandlerInterface;
use App\BlogContext\Application\Operation\Query\GetArticle\Query;
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
            id: $request->id,
        );

        // Execute query through handler
        $article = ($this->handler)($query);

        // Return response with article data
        return new Response(
            id: $article->id->getValue(),
            title: $article->title->getValue(),
            content: $article->content->getValue(),
            slug: $article->slug->getValue(),
            status: $article->status->value,
            authorId: $article->authorId,
            createdAt: $article->timestamps->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $article->timestamps->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            publishedAt: $article->publishedAt?->format(\DateTimeInterface::ATOM),
        );
    }
}
