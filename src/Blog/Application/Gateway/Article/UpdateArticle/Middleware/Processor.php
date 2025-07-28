<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Article\UpdateArticle\Middleware;

use App\Blog\Application\Gateway\Article\UpdateArticle\Request;
use App\Blog\Application\Gateway\Article\UpdateArticle\Response;
use App\Blog\Application\Operation\Command\Article\UpdateArticle\Command;
use App\Blog\Application\Operation\Command\Article\UpdateArticle\Handler;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Shared\Service\SlugGeneratorInterface;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
        private SlugGeneratorInterface $slugGenerator,
    ) {
    }

    public function __invoke(GatewayRequest $gatewayRequest): GatewayResponse
    {
        /** @var Request $gatewayRequest */

        // If title is provided but slug is not, generate slug from title
        $finalSlug = $gatewayRequest->slug;
        if (null !== $gatewayRequest->title && null === $gatewayRequest->slug) {
            $title = new Title($gatewayRequest->title);
            $generatedSlug = $this->slugGenerator->generateFromTitle($title);
            $finalSlug = $generatedSlug->getValue();
        }

        // Create command
        $command = new Command(
            articleId: $gatewayRequest->articleId,
            title: $gatewayRequest->title,
            content: $gatewayRequest->content,
            slug: $finalSlug,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return success response
        return new Response(
            success: true,
            message: 'Article updated successfully',
            slug: $finalSlug,
        );
    }
}
