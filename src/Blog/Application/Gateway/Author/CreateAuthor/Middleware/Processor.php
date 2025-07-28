<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Author\CreateAuthor\Middleware;

use App\Blog\Application\Gateway\Author\CreateAuthor\Request;
use App\Blog\Application\Gateway\Author\CreateAuthor\Response;
use App\Blog\Application\Operation\Command\Author\CreateAuthor\Command;
use App\Blog\Application\Operation\Command\Author\CreateAuthor\HandlerInterface;
use App\Blog\Application\Shared\Generator\AuthorIdGeneratorInterface;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private HandlerInterface $handler,
        private AuthorIdGeneratorInterface $authorIdGenerator,
    ) {
    }

    public function __invoke(GatewayRequest $gatewayRequest): GatewayResponse
    {
        /** @var Request $gatewayRequest */

        // Generate ID before creating command
        $authorId = $this->authorIdGenerator->nextIdentity();

        // Create command with generated ID
        $command = new Command(
            authorId: $authorId,
            name: $gatewayRequest->name,
            email: $gatewayRequest->email,
            bio: $gatewayRequest->bio ?? '',
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return response with generated ID
        return new Response(
            authorId: $authorId->getValue(),
            success: true,
        );
    }
}
