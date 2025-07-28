<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\DeleteTag\Middleware;

use App\Blog\Application\Gateway\Tag\DeleteTag\Request;
use App\Blog\Application\Gateway\Tag\DeleteTag\Response;
use App\Blog\Application\Operation\Command\Tag\DeleteTag\Command;
use App\Blog\Application\Operation\Command\Tag\DeleteTag\Handler;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        // Create command
        $command = new Command(
            tagId: $request->id,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return success response
        return new Response();
    }
}
