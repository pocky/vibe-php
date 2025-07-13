<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateArticle\Middleware;

use App\BlogContext\Application\Gateway\CreateArticle\Request;
use App\BlogContext\Application\Gateway\CreateArticle\Response;
use App\BlogContext\Application\Operation\Command\CreateArticle\Command;
use App\BlogContext\Infrastructure\Identity\ArticleIdGenerator;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Infrastructure\MessageBus\CommandBusInterface;

final readonly class Processor
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private ArticleIdGenerator $articleIdGenerator,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */
        // Generate unique article ID using domain service
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

        // Execute via Command Bus
        ($this->commandBus)($command);

        // Transform to Gateway Response using command data
        return new Response(
            articleId: $articleId->getValue(),
            slug: $request->slug,
            status: $request->status,
            createdAt: $request->createdAt,
        );
    }
}
