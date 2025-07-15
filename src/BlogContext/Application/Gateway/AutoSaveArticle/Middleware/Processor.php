<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\AutoSaveArticle\Middleware;

use App\BlogContext\Application\Gateway\AutoSaveArticle\Request;
use App\BlogContext\Application\Gateway\AutoSaveArticle\Response;
use App\BlogContext\Application\Operation\Command\AutoSaveArticle\Command;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Content, Title};
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
        );

        ($this->commandBus)($command);

        return new Response(
            articleId: $request->articleId,
            title: $request->title,
            content: $request->content,
            autoSavedAt: new \DateTimeImmutable(),
        );
    }
}
