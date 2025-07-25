<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\DeleteCategory;

interface HandlerInterface
{
    public function __invoke(Command $command): void;
}
