<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Infrastructure\Identity;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Infrastructure\Identity\ArticleIdGenerator;
use App\Shared\Infrastructure\Generator\UuidGenerator;

trait ArticleIdGeneratorTrait
{
    private ArticleIdGenerator $articleIdGenerator;

    protected function getArticleIdGenerator(): ArticleIdGenerator
    {
        if (!isset($this->articleIdGenerator)) {
            $this->articleIdGenerator = new ArticleIdGenerator(new UuidGenerator());
        }

        return $this->articleIdGenerator;
    }

    protected function generateArticleId(): ArticleId
    {
        return $this->getArticleIdGenerator()->nextIdentity();
    }

    protected function generateArticleIds(int $count): array
    {
        $ids = [];
        for ($i = 0; $i < $count; ++$i) {
            $ids[] = $this->generateArticleId();
        }

        return $ids;
    }
}
