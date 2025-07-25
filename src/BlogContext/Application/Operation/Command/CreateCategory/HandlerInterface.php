<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateCategory;

interface HandlerInterface
{
    public function __invoke(Command $command): void;
}
