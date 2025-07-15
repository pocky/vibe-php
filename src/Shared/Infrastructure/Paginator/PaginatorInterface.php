<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Paginator;

interface PaginatorInterface
{
    public function getItems(): array;

    public function getTotalItems(): int;

    public function getCurrentPage(): int;

    public function getItemsPerPage(): int;

    public function hasNextPage(): bool;
}
