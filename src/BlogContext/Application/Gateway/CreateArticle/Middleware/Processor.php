<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateArticle\Middleware;

use App\BlogContext\Application\Gateway\CreateArticle\Request;
use App\BlogContext\Application\Gateway\CreateArticle\Response;
use App\BlogContext\Application\Operation\Command\CreateArticle\Command;
use App\BlogContext\Application\Operation\Command\CreateArticle\HandlerInterface;
use App\BlogContext\Domain\Shared\Generator\ArticleIdGeneratorInterface;
use App\BlogContext\Domain\Shared\Service\SlugGeneratorInterface;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private HandlerInterface $handler,
        private ArticleIdGeneratorInterface $idGenerator,
        private SlugGeneratorInterface $slugGenerator,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */

        try {
            // Generate ID
            $articleId = $this->idGenerator->nextIdentity();

            // Generate slug from title if not provided
            $slug = $request->slug ?? $this->slugGenerator->generateFromTitle(new Title($request->title))->getValue();

            // Create command with all necessary data
            $command = new Command(
                articleId: $articleId->getValue(),
                title: $request->title,
                content: $request->content,
                slug: $slug,
                authorId: $request->authorId,
            );

            // Execute command through handler
            ($this->handler)($command);

            // Return response with generated data
            return new Response(
                success: true,
                message: 'Article created successfully',
                articleId: $articleId->getValue(),
                slug: $slug,
            );
        } catch (\Throwable $e) {
            return new Response(
                success: false,
                message: $e->getMessage(),
            );
        }
    }
}
