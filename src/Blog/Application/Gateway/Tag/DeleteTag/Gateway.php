<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\DeleteTag;

use App\Blog\Application\Gateway\Tag\DeleteTag\Middleware\Processor;
use App\Blog\Application\Operation\Command\Tag\DeleteTag\Handler;

final readonly class Gateway
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $processor = new Processor($this->handler);

        return $processor($request);
    }
}
