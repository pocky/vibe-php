<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Author\CreateAuthor;

interface HandlerInterface
{
    public function __invoke(Command $command): void;
}
