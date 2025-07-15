<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetArticle\Middleware;

use App\BlogContext\Application\Gateway\GetArticle\Request;
use App\BlogContext\Application\Gateway\GetArticle\Response;
use App\BlogContext\Application\Operation\Query\GetArticle\Handler;
use App\BlogContext\Application\Operation\Query\GetArticle\Query;
use App\BlogContext\Domain\Shared\Model\Article;
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
        $article = ($this->handler)($query);

        $articleData = [];
        if ($article instanceof Article) {
            $articleData = [
                'id' => $article->getId()->toString(),
                'title' => $article->getTitle()->getValue(),
                'content' => $article->getContent()->getValue(),
                'slug' => $article->getSlug()->getValue(),
                'status' => $article->getStatus()->value,
                'created_at' => $article->getCreatedAt()->format('c'),
                'updated_at' => $article->getUpdatedAt()->format('c'),
                'published_at' => $article->getPublishedAt()?->format('c'),
            ];
        }

        return new Response(article: $articleData);
    }
}
