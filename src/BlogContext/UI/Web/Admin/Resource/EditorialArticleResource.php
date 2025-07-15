<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Resource;

use App\BlogContext\UI\Web\Admin\Grid\EditorialArticleGrid;
use App\BlogContext\UI\Web\Admin\Provider\EditorialArticleItemProvider;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Show;
use Sylius\Resource\Model\ResourceInterface;

#[AsResource(
    alias: 'app.editorial_article',
    section: 'admin',
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: 'doctrine/orm',
)]
#[Index(
    grid: EditorialArticleGrid::class,
)]
#[Show(
    provider: EditorialArticleItemProvider::class,
)]
final class EditorialArticleResource implements ResourceInterface
{
    public function __construct(
        public string|null $id = null,
        public string|null $title = null,
        public string|null $content = null,
        public string|null $slug = null,
        public string|null $status = null,
        public string|null $authorId = null,
        public string|null $authorName = null,
        public \DateTimeInterface|null $submittedAt = null,
        public \DateTimeInterface|null $reviewedAt = null,
        public string|null $reviewerId = null,
        public string|null $reviewerName = null,
        public string|null $reviewDecision = null,
        public string|null $reviewReason = null,
    ) {
    }

    public function getId(): string|null
    {
        return $this->id;
    }
}
