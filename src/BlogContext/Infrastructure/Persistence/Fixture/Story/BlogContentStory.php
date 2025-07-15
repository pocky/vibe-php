<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Fixture\Story;

use App\BlogContext\Infrastructure\Persistence\Fixture\Factory\BlogArticleFactory;
use App\BlogContext\Infrastructure\Persistence\Fixture\Factory\BlogEditorialCommentFactory;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\Story;

final class BlogContentStory extends Story
{
    public function build(): void
    {
        // Define some consistent IDs for related data
        $reviewerId1 = Uuid::v7()->toRfc4122();
        $reviewerId2 = Uuid::v7()->toRfc4122();
        $authorId1 = Uuid::v7()->toRfc4122();
        $authorId2 = Uuid::v7()->toRfc4122();

        // Create some random published articles
        /** @phpstan-ignore-next-line */
        BlogArticleFactory::new()
            ->published()
            ->withAuthor($authorId1)
            ->many(3)
            ->create();

        // Create some draft articles
        /** @phpstan-ignore-next-line */
        BlogArticleFactory::new()
            ->draft()
            ->withAuthor($authorId2)
            ->many(2)
            ->create();

        // Create articles pending review (the main scenario for our feature)
        /** @phpstan-ignore-next-line */
        $pendingArticles = BlogArticleFactory::new()
            ->pendingReview()
            ->withAuthor($authorId1)
            ->many(4)
            ->create();

        // Create approved articles
        /** @phpstan-ignore-next-line */
        $approvedArticles = BlogArticleFactory::new()
            ->approved()
            ->withAuthor($authorId2)
            ->withReviewer($reviewerId1)
            ->many(2)
            ->create();

        // Create rejected articles
        /** @phpstan-ignore-next-line */
        $rejectedArticles = BlogArticleFactory::new()
            ->rejected()
            ->withAuthor($authorId1)
            ->withReviewer($reviewerId2)
            ->many(2)
            ->create();

        // Add editorial comments to some articles
        foreach ($pendingArticles as $article) {
            /** @phpstan-ignore-next-line */
            BlogEditorialCommentFactory::new()
                ->forArticle($article->getId()->toRfc4122())
                ->byReviewer($reviewerId1)
                ->generalComment()
                ->create();

            /** @phpstan-ignore-next-line */
            BlogEditorialCommentFactory::new()
                ->forArticle($article->getId()->toRfc4122())
                ->byReviewer($reviewerId1)
                ->inlineComment()
                ->many(2)
                ->create();
        }

        // Add comments to approved articles (showing review history)
        foreach ($approvedArticles as $article) {
            /** @phpstan-ignore-next-line */
            BlogEditorialCommentFactory::new()
                ->forArticle($article->getId()->toRfc4122())
                ->byReviewer($reviewerId1)
                ->withComment('Excellent work! This article is ready for publication.')
                ->create();
        }

        // Add comments to rejected articles
        foreach ($rejectedArticles as $article) {
            /** @phpstan-ignore-next-line */
            BlogEditorialCommentFactory::new()
                ->forArticle($article->getId()->toRfc4122())
                ->byReviewer($reviewerId2)
                ->withComment('This article needs significant revision before it can be published.')
                ->create();

            /** @phpstan-ignore-next-line */
            BlogEditorialCommentFactory::new()
                ->forArticle($article->getId()->toRfc4122())
                ->byReviewer($reviewerId2)
                ->inlineComment()
                ->many(3)
                ->create();
        }

        // Create a special article for demo purposes
        /** @phpstan-ignore-next-line */
        $demoArticle = BlogArticleFactory::new()
            ->withTitle('The Future of Web Development: Trends and Technologies')
            ->withSlug('future-web-development-trends')
            ->pendingReview()
            ->withAuthor($authorId1)
            ->create();

        // Add comprehensive editorial comments to the demo article
        /** @phpstan-ignore-next-line */
        BlogEditorialCommentFactory::new()
            ->forArticle($demoArticle->getId()->toRfc4122())
            ->byReviewer($reviewerId1)
            ->withComment('This is a well-researched article with great potential. Please address the following comments before publication.')
            ->create();

        /** @phpstan-ignore-next-line */
        BlogEditorialCommentFactory::new()
            ->forArticle($demoArticle->getId()->toRfc4122())
            ->byReviewer($reviewerId1)
            ->withSelection('Web development is constantly evolving', 0, 40)
            ->withComment('Consider adding specific examples of recent evolution in this area.')
            ->create();

        /** @phpstan-ignore-next-line */
        BlogEditorialCommentFactory::new()
            ->forArticle($demoArticle->getId()->toRfc4122())
            ->byReviewer($reviewerId1)
            ->withSelection('The latest trends show', 500, 520)
            ->withComment('This section needs more recent data to support the claims.')
            ->create();
    }
}
