<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'blog_authors')]
#[ORM\Index(columns: ['email'], name: 'idx_authors_email')]
#[ORM\Index(columns: ['name'], name: 'idx_authors_name')]
class Author
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        public Uuid $id,
        #[ORM\Column(type: Types::STRING, length: 200)]
        public string $name,
        #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
        public string $email,
        #[ORM\Column(type: Types::TEXT)]
        public string $bio,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeImmutable $createdAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public \DateTimeImmutable $updatedAt
    ) {
    }
}
