<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Fixture\Story;

use App\BlogContext\Infrastructure\Persistence\Fixture\Factory\BlogArticleFactory;
use Zenstruck\Foundry\Story;

final class BlogContentStory extends Story
{
    public function build(): void
    {
        // Create some random published articles
        BlogArticleFactory::new()
            ->published()
            ->many(5)
            ->create();

        // Create some draft articles
        BlogArticleFactory::new()
            ->draft()
            ->many(3)
            ->create();
    }
}
