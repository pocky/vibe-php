<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateCategory\Middleware;

use App\BlogContext\Application\Gateway\CreateCategory\Request;
use App\BlogContext\Application\Gateway\CreateCategory\Response;
use App\BlogContext\Application\Operation\Command\CreateCategory\Command;
use App\BlogContext\Application\Operation\Command\CreateCategory\Handler;
use App\BlogContext\Infrastructure\Identity\CategoryIdGenerator;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
        private CategoryIdGenerator $idGenerator,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */

        // Generate new category ID
        $categoryId = $this->idGenerator->nextIdentity();

        // Create command
        $command = new Command(
            categoryId: $categoryId->getValue(),
            name: $request->name,
            slug: $request->slug,
            parentId: $request->parentId,
            createdAt: $request->createdAt,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Build path for response (simplified for now)
        $path = $request->parentId
            ? 'parent/' . $request->slug // This would need proper parent lookup in a real implementation
            : $request->slug;

        // Return response with generated ID
        return new Response(
            categoryId: $categoryId->getValue(),
            name: $request->name,
            slug: $request->slug,
            path: $path,
            parentId: $request->parentId,
            createdAt: $request->createdAt ?? new \DateTimeImmutable(),
        );
    }
}
