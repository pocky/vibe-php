<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Article\GetArticle\Middleware;

use App\Blog\Application\Gateway\Article\GetArticle\Request;
use App\Blog\Application\Gateway\Article\GetArticle\Response;
use App\Blog\Application\Operation\Query\Article\GetArticle\Handler;
use App\Blog\Application\Operation\Query\Article\GetArticle\Query;
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
        $articleQuery = new Query(
            id: $gatewayRequest->id,
        );

        // Execute query through handler
        $articleReadModel = ($this->handler)($articleQuery);

        // Return response with article data
        return new Response(
            id: $articleReadModel->id->getValue(),
            title: $articleReadModel->title->getValue(),
            content: $articleReadModel->content->getValue(),
            slug: $articleReadModel->slug->getValue(),
            status: $articleReadModel->status->value,
            authorId: $articleReadModel->authorId,
            createdAt: $articleReadModel->timestamps->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $articleReadModel->timestamps->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            publishedAt: $articleReadModel->publishedAt?->format(\DateTimeInterface::ATOM),
        );
    }
}
