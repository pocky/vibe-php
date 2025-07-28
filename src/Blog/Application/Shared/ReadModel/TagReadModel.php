<?php

declare(strict_types=1);

namespace App\Blog\Application\Shared\ReadModel;

use App\Blog\Domain\Shared\ValueObject\Timestamps;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;

/**
 * Read model for tag data.
 * Used for query operations and represents the current state of a tag.
 */
final readonly class TagReadModel
{
    public function __construct(
        public TagId $id,
        public TagName $name,
        public TagSlug $slug,
        public Timestamps $timestamps,
    ) {
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->timestamps->getCreatedAt();
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->timestamps->getUpdatedAt();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'name' => $this->name->getValue(),
            'slug' => $this->slug->getValue(),
            'createdAt' => $this->timestamps->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->timestamps->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
