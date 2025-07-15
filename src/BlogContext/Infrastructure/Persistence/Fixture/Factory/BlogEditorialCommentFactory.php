<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Fixture\Factory;

use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogEditorialComment;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<BlogEditorialComment>
 */
final class BlogEditorialCommentFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return BlogEditorialComment::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'id' => Uuid::v7(),
            'articleId' => Uuid::v7(),
            'reviewerId' => Uuid::v7(),
            'comment' => self::faker()->paragraphs(2, true),
            'selectedText' => null,
            'positionStart' => null,
            'positionEnd' => null,
            'createdAt' => new \DateTimeImmutable(),
        ];
    }

    public function forArticle(string $articleId): static
    {
        return $this->with([
            'articleId' => Uuid::fromString($articleId),
        ]);
    }

    public function byReviewer(string $reviewerId): static
    {
        return $this->with([
            'reviewerId' => Uuid::fromString($reviewerId),
        ]);
    }

    public function withSelection(string $selectedText, int $positionStart, int $positionEnd): static
    {
        return $this->with([
            'selectedText' => $selectedText,
            'positionStart' => $positionStart,
            'positionEnd' => $positionEnd,
        ]);
    }

    public function withComment(string $comment): static
    {
        return $this->with([
            'comment' => $comment,
        ]);
    }

    public function generalComment(): static
    {
        return $this->with([
            'comment' => self::faker()->randomElement([
                'This article needs more detailed examples to support the main points.',
                'Consider reorganizing the structure for better flow.',
                'The conclusion could be stronger with a clear call to action.',
                'Great research, but some citations need to be updated.',
                'This section is well-written and engaging.',
            ]),
            'selectedText' => null,
            'positionStart' => null,
            'positionEnd' => null,
        ]);
    }

    public function inlineComment(): static
    {
        $sentences = [
            'This paragraph could use more clarity.',
            'Consider simplifying this sentence.',
            'Excellent point, well articulated.',
            'This needs a supporting reference.',
            'Grammar correction needed here.',
        ];

        return $this->with([
            'comment' => self::faker()->randomElement($sentences),
            'selectedText' => self::faker()->sentence(4),
            'positionStart' => self::faker()->numberBetween(100, 500),
            'positionEnd' => self::faker()->numberBetween(501, 700),
        ]);
    }
}
