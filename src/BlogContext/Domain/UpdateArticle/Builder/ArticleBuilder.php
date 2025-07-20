<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateArticle\Builder;

use App\BlogContext\Domain\Shared\Builder\ArticleBuilderInterface;
use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\BlogContext\Domain\UpdateArticle\Model\Article;

/**
 * Builder for creating Article models in the UpdateArticle context.
 */
final class ArticleBuilder implements ArticleBuilderInterface
{
    public function fromReadModel(ArticleReadModel $readModel): Article
    {
        return new Article(
            id: $readModel->id,
            title: $readModel->title,
            content: $readModel->content,
            slug: $readModel->slug,
            status: $readModel->status,
            authorId: $readModel->authorId,
            timestamps: $readModel->timestamps,
            publishedAt: $readModel->publishedAt
        );
    }

    public function fromArray(array $data): Article
    {
        throw new \LogicException('UpdateArticle builder should use fromReadModel method');
    }

    /**
     * Create an updated article with changes.
     *
     * @param array{title?: Title, content?: Content, slug?: Slug} $changes
     */
    public function withChanges(
        ArticleReadModel $original,
        array $changes,
        array $events = []
    ): Article {
        return new Article(
            id: $original->id,
            title: $changes['title'] ?? $original->title,
            content: $changes['content'] ?? $original->content,
            slug: $changes['slug'] ?? $original->slug,
            status: $original->status,
            authorId: $original->authorId,
            timestamps: $original->timestamps->withUpdatedAt(new \DateTimeImmutable()),
            publishedAt: $original->publishedAt,
            events: $events,
            changes: $this->buildChangesArray($original, $changes)
        );
    }

    private function buildChangesArray(ArticleReadModel $original, array $newValues): array
    {
        $changes = [];

        if (isset($newValues['title']) && $newValues['title'] instanceof Title && !$original->title->equals($newValues['title'])) {
            $changes['title'] = [
                'old' => $original->title->getValue(),
                'new' => $newValues['title']->getValue(),
            ];
        }

        if (isset($newValues['content']) && $newValues['content'] instanceof Content && !$original->content->equals($newValues['content'])) {
            $changes['content'] = [
                'old' => $original->content->getValue(),
                'new' => $newValues['content']->getValue(),
            ];
        }

        if (isset($newValues['slug']) && $newValues['slug'] instanceof Slug && !$original->slug->equals($newValues['slug'])) {
            $changes['slug'] = [
                'old' => $original->slug->getValue(),
                'new' => $newValues['slug']->getValue(),
            ];
        }

        return $changes;
    }
}
