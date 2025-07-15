<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateArticle\Middleware;

use App\BlogContext\Application\Gateway\UpdateArticle\Request;
use App\BlogContext\Application\Gateway\UpdateArticle\Response;
use App\BlogContext\Application\Operation\Command\UpdateArticle\Command;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Infrastructure\MessageBus\CommandBusInterface;

final readonly class Processor
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */
        $command = new Command(
            articleId: new ArticleId($request->articleId),
            title: new Title($request->title),
            content: new Content($request->content),
            slug: new Slug($request->slug),
            status: ArticleStatus::fromString($request->status),
        );

        ($this->commandBus)($command);

        return new Response(
            articleId: $request->articleId,
            title: $request->title,
            content: $request->content,
            slug: $request->slug,
            status: $request->status,
            updatedAt: new \DateTimeImmutable(),
        );
    }
}
