<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ApproveArticle\Middleware;

use App\BlogContext\Application\Gateway\ApproveArticle\Request;
use App\BlogContext\Application\Gateway\ApproveArticle\Response;
use App\BlogContext\Application\Operation\Command\ApproveArticle\Command;
use App\BlogContext\Application\Operation\Command\ApproveArticle\Handler;
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
            throw new \RuntimeException('Invalid request type for ApproveArticle processor');
        }

        $command = new Command(
            articleId: $request->articleId,
            reviewerId: $request->reviewerId,
            reason: $request->reason,
        );

        ($this->handler)($command);

        return new Response(
            articleId: $request->articleId,
            status: 'approved',
            reviewerId: $request->reviewerId,
            reviewedAt: new \DateTimeImmutable(),
            approvalReason: $request->reason,
        );
    }
}
