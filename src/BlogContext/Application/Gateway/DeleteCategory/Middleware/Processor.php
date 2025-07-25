<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\DeleteCategory\Middleware;

use App\BlogContext\Application\Gateway\DeleteCategory\Request;
use App\BlogContext\Application\Gateway\DeleteCategory\Response;
use App\BlogContext\Application\Operation\Command\DeleteCategory\Command;
use App\BlogContext\Application\Operation\Command\DeleteCategory\HandlerInterface;
use App\BlogContext\Domain\DeleteCategory\Exception\CategoryNotFound;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private HandlerInterface $handler,
    ) {
    }

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        /** @var Request $request */
        try {
            $command = new Command(
                categoryId: $request->categoryId,
            );

            ($this->handler)($command);

            return new Response(
                categoryId: $request->categoryId,
                deletedAt: new \DateTimeImmutable(),
            );
        } catch (CategoryNotFound $e) {
            throw new \RuntimeException($e->getMessage(), 404, $e);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 400, $e);
        }
    }
}
