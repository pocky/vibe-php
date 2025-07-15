<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\PublishArticle\Middleware;

use App\BlogContext\Application\Gateway\PublishArticle\Request;
use App\BlogContext\Application\Gateway\PublishArticle\Response;
use App\BlogContext\Application\Operation\Command\PublishArticle\Command;
use App\BlogContext\Domain\PublishArticle\Exception\ArticleAlreadyPublished;
use App\BlogContext\Domain\PublishArticle\Exception\ArticleNotFound;
use App\BlogContext\Domain\PublishArticle\Exception\ArticleNotReady;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
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
        );

        try {
            /** @var \App\BlogContext\Domain\PublishArticle\DataPersister\Article $publishedArticle */
            $publishedArticle = ($this->commandBus)($command);

            return new Response(
                articleId: $request->articleId,
                status: 'published',
                publishedAt: $publishedArticle->publishedAt,
            );
        } catch (ArticleNotFound|ArticleAlreadyPublished|ArticleNotReady $e) {
            // Re-throw domain exceptions so they can be caught by DefaultErrorHandler
            throw $e;
        } catch (\Throwable $e) {
            // Check if this is a wrapped domain exception
            $previous = $e->getPrevious();
            if ($previous instanceof ArticleNotFound
                || $previous instanceof ArticleAlreadyPublished
                || $previous instanceof ArticleNotReady) {
                throw $previous;
            }

            // Check nested previous exceptions (Messenger may wrap multiple times)
            $current = $e;
            while ($current = $current->getPrevious()) {
                if ($current instanceof ArticleNotFound
                    || $current instanceof ArticleAlreadyPublished
                    || $current instanceof ArticleNotReady) {
                    throw $current;
                }
            }

            // Re-throw if not a domain exception
            throw $e;
        }
    }
}
