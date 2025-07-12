<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Fixture\Factory;

use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogArticle;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<BlogArticle>
 */
final class BlogArticleFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return BlogArticle::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        $status = self::faker()->randomElement(['draft', 'published']);

        return [
            'id' => Uuid::v7(),
            'title' => self::faker()->sentence(4),
            'content' => self::faker()->paragraphs(3, true),
            'slug' => self::faker()->slug(),
            'status' => $status,
            'createdAt' => new \DateTimeImmutable(),
            'updatedAt' => null,
            'publishedAt' => 'published' === $status ? new \DateTimeImmutable() : null,
        ];
    }

    public function draft(): static
    {
        return $this->with([
            'status' => 'draft',
            'publishedAt' => null,
        ]);
    }

    public function published(): static
    {
        return $this->with([
            'status' => 'published',
            'publishedAt' => new \DateTimeImmutable(),
        ]);
    }

    public function withTitle(string $title): static
    {
        return $this->with([
            'title' => $title,
            'slug' => self::faker()->slug(),
        ]);
    }

    public function withSlug(string $slug): static
    {
        return $this->with([
            'slug' => $slug,
        ]);
    }
}
