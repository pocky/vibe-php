<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle;

use App\BlogContext\Domain\PublishArticle\DataPersister\Article;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

interface PublisherInterface
{
    public function __invoke(ArticleId $articleId): Article;
}
