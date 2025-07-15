<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\RejectArticle\Middleware;

use App\BlogContext\Application\Gateway\RejectArticle\Request;
use App\BlogContext\Application\Gateway\RejectArticle\Response;
use App\BlogContext\Application\Operation\Command\RejectArticle\Command;
use App\BlogContext\Application\Operation\Command\RejectArticle\Handler;
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
        if (!$request instanceof Request) {
            throw new \RuntimeException('Invalid request type for RejectArticle processor');
        }

        $command = new Command(
            articleId: $request->articleId,
            reviewerId: $request->reviewerId,
            reason: $request->reason,
        );

        ($this->handler)($command);

        return new Response(
            articleId: $request->articleId,
            status: 'rejected',
            reviewerId: $request->reviewerId,
            reviewedAt: new \DateTimeImmutable(),
            reason: $request->reason,
        );
    }
}
