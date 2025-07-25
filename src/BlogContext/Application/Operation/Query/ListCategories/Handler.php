<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\ListCategories;

use App\BlogContext\Domain\GetCategories\ListCriteria;
use App\BlogContext\Domain\GetCategories\ListerInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;

final readonly class Handler implements HandlerInterface
{
    public function __construct(
        private ListerInterface $lister,
    ) {
    }

    public function __invoke(Query $query): View
    {
        $criteria = new ListCriteria(
            page: $query->page,
            limit: $query->limit,
            sortBy: $query->sortBy ?? 'order',
            sortOrder: strtoupper($query->sortOrder ?? 'ASC'),
            parentId: $query->parentId ? new CategoryId($query->parentId) : null,
        );

        $data = ($this->lister)($criteria);

        return new View(
            categories: array_map(
                fn ($category) => new CategoryView(
                    id: $category->id->getValue(),
                    name: $category->name->getValue(),
                    slug: $category->slug->getValue(),
                    description: $category->description instanceof \App\BlogContext\Domain\Shared\ValueObject\Description ? $category->description->getValue() : null,
                    parentId: $category->parentId?->getValue(),
                    order: $category->order->getValue(),
                    createdAt: $category->createdAt->format(\DateTimeInterface::ATOM),
                    updatedAt: $category->updatedAt->format(\DateTimeInterface::ATOM),
                ),
                $data->categories,
            ),
            total: $data->total,
            page: $data->page,
            limit: $data->limit,
        );
    }
}
