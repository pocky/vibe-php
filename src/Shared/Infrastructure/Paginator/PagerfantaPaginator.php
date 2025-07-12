<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Paginator;

use Pagerfanta\PagerfantaInterface;

final readonly class PagerfantaPaginator implements PaginatorInterface
{
    public function __construct(
        private PagerfantaInterface $pagerfanta,
    ) {
    }

    public function getIterator(): \Traversable
    {
        return $this->pagerfanta->getIterator();
    }

    public function count(): int
    {
        return iterator_count($this->pagerfanta->getIterator());
    }

    public function getCurrentPage(): int
    {
        return $this->pagerfanta->getCurrentPage();
    }

    public function getItemsPerPage(): int
    {
        return $this->pagerfanta->getMaxPerPage();
    }

    public function getLastPage(): int
    {
        return $this->pagerfanta->getNbPages();
    }

    public function getTotalItems(): int
    {
        return $this->pagerfanta->getNbResults();
    }
}
