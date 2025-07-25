<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateCategory\Middleware;

use App\BlogContext\Application\Gateway\CreateCategory\Request;
use App\BlogContext\Application\Gateway\CreateCategory\Response;
use App\BlogContext\Application\Operation\Command\CreateCategory\Command;
use App\BlogContext\Application\Operation\Command\CreateCategory\HandlerInterface;
use App\BlogContext\Domain\Shared\Generator\CategoryIdGeneratorInterface;
use App\BlogContext\Domain\Shared\Service\SlugGeneratorInterface;
use App\BlogContext\Domain\Shared\ValueObject\Name;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private HandlerInterface $handler,
        private CategoryIdGeneratorInterface $idGenerator,
        private SlugGeneratorInterface $slugGenerator,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */

        try {
            // Generate ID
            $categoryId = $this->idGenerator->nextIdentity();

            // Generate slug from name
            $slug = $this->slugGenerator->generateFromName(new Name($request->name))->getValue();

            // Create command with all necessary data
            $command = new Command(
                categoryId: $categoryId->getValue(),
                name: $request->name,
                slug: $slug,
                description: $request->description,
                parentCategoryId: $request->parentCategoryId,
                order: $request->order,
            );

            // Execute command through handler
            ($this->handler)($command);

            // Return response with generated data
            return new Response(
                success: true,
                message: 'Category created successfully',
                categoryId: $categoryId->getValue(),
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
