<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateCategory\Middleware;

use App\BlogContext\Application\Gateway\UpdateCategory\Request;
use App\BlogContext\Application\Gateway\UpdateCategory\Response;
use App\BlogContext\Application\Operation\Command\UpdateCategory\Command;
use App\BlogContext\Application\Operation\Command\UpdateCategory\HandlerInterface;
use App\BlogContext\Domain\UpdateCategory\Exception\CategoryNotFound;
use App\BlogContext\Domain\UpdateCategory\Exception\SlugAlreadyExists;
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
                name: $request->name,
                slug: $request->slug,
                description: $request->description,
                parentId: $request->parentId,
                order: $request->order,
            );

            ($this->handler)($command);

            return new Response(
                categoryId: $request->categoryId,
                name: $request->name,
                slug: $request->slug,
                description: $request->description,
                parentId: $request->parentId,
                order: $request->order,
                updatedAt: new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
            );
        } catch (CategoryNotFound $e) {
            throw new \RuntimeException($e->getMessage(), 404, $e);
        } catch (SlugAlreadyExists $e) {
            throw new \InvalidArgumentException($e->getMessage(), 409, $e);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 400, $e);
        }
    }
}
