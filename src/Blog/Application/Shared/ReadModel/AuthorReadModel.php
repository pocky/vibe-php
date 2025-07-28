<?php

declare(strict_types=1);

namespace App\Blog\Application\Shared\ReadModel;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorBio;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorName;
use App\Blog\Domain\Shared\ValueObject\Timestamps;

/**
 * Read model for author data.
 * Used for query operations and represents the current state of an author.
 */
final readonly class AuthorReadModel
{
    public function __construct(
        public AuthorId $id,
        public AuthorName $name,
        public AuthorEmail $email,
        public AuthorBio $bio,
        public Timestamps $timestamps,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'name' => $this->name->getValue(),
            'email' => $this->email->getValue(),
            'bio' => $this->bio->getValue(),
            'createdAt' => $this->timestamps->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->timestamps->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
