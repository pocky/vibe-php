<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetArticle\Middleware;

use App\BlogContext\Application\Gateway\GetArticle\Request;
use App\BlogContext\Application\Gateway\GetArticle\Response;
use App\BlogContext\Application\Operation\Query\GetArticle\Handler;
use App\BlogContext\Application\Operation\Query\GetArticle\Query;
use App\BlogContext\Domain\Shared\Repository\ArticleData;
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
        $query = new Query($request->id);
        $articleData = ($this->handler)($query);

        $article = [];
        if ($articleData instanceof ArticleData) {
            $article = [
                'id' => $articleData->id->toString(),
                'title' => $articleData->title->getValue(),
                'content' => $articleData->content->getValue(),
                'slug' => $articleData->slug->getValue(),
                'status' => $articleData->status->value,
                'created_at' => $articleData->createdAt->format('c'),
                'updated_at' => ($articleData->updatedAt ?? $articleData->createdAt)->format('c'),
                'published_at' => $articleData->publishedAt?->format('c'),
            ];
        }

        return new Response(article: $article);
    }
}
