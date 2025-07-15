<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\AddEditorialComment\Middleware;

use App\BlogContext\Application\Gateway\AddEditorialComment\Request;
use App\BlogContext\Application\Gateway\AddEditorialComment\Response;
use App\BlogContext\Application\Operation\Command\AddEditorialComment\Command;
use App\BlogContext\Application\Operation\Command\AddEditorialComment\Handler;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */

        $command = new Command(
            articleId: $request->articleId,
            reviewerId: $request->reviewerId,
            comment: $request->comment,
            selectedText: $request->selectedText,
            positionStart: $request->positionStart,
            positionEnd: $request->positionEnd,
        );

        $result = ($this->handler)($command);

        return new Response(
            id: $result->id,
            articleId: $result->articleId,
            reviewerId: $result->reviewerId,
            comment: $result->comment,
            createdAt: $result->createdAt,
            selectedText: $result->selectedText,
            positionStart: $result->positionStart,
            positionEnd: $result->positionEnd,
        );
    }
}
