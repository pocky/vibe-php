<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Persistence\Fixture;

use App\BlogContext\Infrastructure\Persistence\Fixture\Story\BlogContentStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ArticleFixtures extends Fixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        new BlogContentStory()->build();
    }
}
