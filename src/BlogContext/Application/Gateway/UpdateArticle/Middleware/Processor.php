<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateArticle\Middleware;

use App\BlogContext\Application\Gateway\UpdateArticle\Request;
use App\BlogContext\Application\Gateway\UpdateArticle\Response;
use App\BlogContext\Application\Operation\Command\UpdateArticle\Command;
use App\BlogContext\Application\Operation\Command\UpdateArticle\HandlerInterface;
use App\BlogContext\Domain\Shared\Service\SlugGeneratorInterface;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private HandlerInterface $handler,
        private SlugGeneratorInterface $slugGenerator,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */

        // If title is provided but slug is not, generate slug from title
        $finalSlug = $request->slug;
        if (null !== $request->title && null === $request->slug) {
            $title = new Title($request->title);
            $generatedSlug = $this->slugGenerator->generateFromTitle($title);
            $finalSlug = $generatedSlug->getValue();
        }

        // Create command
        $command = new Command(
            articleId: $request->articleId,
            title: $request->title,
            content: $request->content,
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
