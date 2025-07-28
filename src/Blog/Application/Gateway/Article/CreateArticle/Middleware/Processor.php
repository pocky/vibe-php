<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Article\CreateArticle\Middleware;

use App\Blog\Application\Gateway\Article\CreateArticle\Request;
use App\Blog\Application\Gateway\Article\CreateArticle\Response;
use App\Blog\Application\Operation\Command\Article\CreateArticle\Command;
use App\Blog\Application\Operation\Command\Article\CreateArticle\Handler;
use App\Blog\Application\Shared\Generator\ArticleIdGeneratorInterface;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Shared\Service\SlugGeneratorInterface;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
        private ArticleIdGeneratorInterface $articleIdGenerator,
        private SlugGeneratorInterface $slugGenerator,
    ) {
    }

    public function __invoke(GatewayRequest $gatewayRequest): GatewayResponse
    {
        /** @var Request $gatewayRequest */

        try {
            // Generate ID
            $articleId = $this->articleIdGenerator->nextIdentity();

            // Generate slug from title if not provided
            $slug = $gatewayRequest->slug ?? $this->slugGenerator->generateFromTitle(new Title($gatewayRequest->title))->getValue();

            // Create command with all necessary data
            $command = new Command(
                articleId: $articleId->getValue(),
                title: $gatewayRequest->title,
                content: $gatewayRequest->content,
                slug: $slug,
                authorId: $gatewayRequest->authorId,
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
        } catch (\Throwable $throwable) {
            return new Response(
                success: false,
                message: $throwable->getMessage(),
            );
        }
    }
}
