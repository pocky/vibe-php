<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateArticle\Middleware;

use App\BlogContext\Application\Gateway\CreateArticle\Request;
use App\BlogContext\Application\Gateway\CreateArticle\Response;
use App\BlogContext\Application\Operation\Command\CreateArticle\Command;
use App\BlogContext\Application\Operation\Command\CreateArticle\Handler;
use App\BlogContext\Infrastructure\Identity\ArticleIdGenerator;

final readonly class Processor
{
    public function __construct(
        private Handler $commandHandler,
        private ArticleIdGenerator $articleIdGenerator,
    ) {
    }

    /**
     * @param callable(Request): Response|null $next
     */
    public function __invoke(Request $request, callable|null $next = null): Response
    {
        // Generate unique article ID
        $articleId = $this->articleIdGenerator->nextIdentity();

        // Create CQRS Command from Gateway Request
        $command = new Command(
            articleId: $articleId,
            title: $request->title,
            content: $request->content,
            slug: $request->slug,
            status: $request->status,
            createdAt: $request->createdAt,
            authorId: $request->authorId,
        );

        // Execute via Command Handler - no return needed
        ($this->commandHandler)($command);

        // Transform to Gateway Response using command data
        return new Response(
            articleId: $articleId->getValue(),
            slug: $request->slug,
            status: $request->status,
            createdAt: $request->createdAt,
        );
    }
}
