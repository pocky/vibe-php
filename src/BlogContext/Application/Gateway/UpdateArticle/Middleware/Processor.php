<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateArticle\Middleware;

use App\BlogContext\Application\Gateway\UpdateArticle\{Request, Response};
use App\BlogContext\Application\Operation\Command\UpdateArticle\{Command, Handler};

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    /**
     * @param callable(Request): Response|null $next
     */
    public function __invoke(Request $request, callable|null $next = null): Response
    {
        // Create command from request
        $command = new Command(
            articleId: $request->articleId,
            title: $request->title,
            content: $request->content,
        );

        // Execute command and get updated article
        $updatedArticle = ($this->handler)($command);

        // Create response from updated article
        return new Response(
            articleId: $updatedArticle->id->getValue(),
            title: $updatedArticle->title->getValue(),
            slug: $updatedArticle->slug->getValue(),
            status: $updatedArticle->status->getValue(),
            updatedAt: $updatedArticle->updatedAt,
            changedFields: ['title', 'content'], // Could be enhanced to detect actual changes
        );
    }
}
