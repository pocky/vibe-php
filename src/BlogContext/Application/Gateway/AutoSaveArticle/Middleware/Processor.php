<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\AutoSaveArticle\Middleware;

use App\BlogContext\Application\Gateway\AutoSaveArticle\{Request, Response};
use App\BlogContext\Application\Operation\Command\AutoSaveArticle\{Command, Handler};

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

        // Create response focused on auto-save feedback
        return new Response(
            articleId: $updatedArticle->id->getValue(),
            status: $updatedArticle->status->getValue(),
            autoSavedAt: $updatedArticle->updatedAt,
            hasChanges: true,
        );
    }
}
