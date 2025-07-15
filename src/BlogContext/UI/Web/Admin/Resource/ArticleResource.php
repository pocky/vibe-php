<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Resource;

use App\BlogContext\UI\Web\Admin\Form\ArticleType;
use App\BlogContext\UI\Web\Admin\Grid\ArticleGrid;
use App\BlogContext\UI\Web\Admin\Processor\CreateArticleProcessor;
use App\BlogContext\UI\Web\Admin\Processor\DeleteArticleProcessor;
use App\BlogContext\UI\Web\Admin\Processor\UpdateArticleProcessor;
use App\BlogContext\UI\Web\Admin\Provider\ArticleItemProvider;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Create;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Show;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;

#[AsResource(
    alias: 'app.article',
    section: 'admin',
    formType: ArticleType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: 'doctrine/orm',
)]
#[Index(
    grid: ArticleGrid::class,
)]
#[Create(
    processor: CreateArticleProcessor::class,
    redirectToRoute: 'app_admin_article_index',
)]
#[Show(
    provider: ArticleItemProvider::class,
)]
#[Update(
    provider: ArticleItemProvider::class,
    processor: UpdateArticleProcessor::class,
    redirectToRoute: 'app_admin_article_index',
)]
#[Delete(
    provider: ArticleItemProvider::class,
    processor: DeleteArticleProcessor::class,
)]
final class ArticleResource implements ResourceInterface
{
    public function __construct(
        public string|null $id = null,
        public string|null $title = null,
        public string|null $content = null,
        public string|null $slug = null,
        public string|null $status = null,
        public \DateTimeInterface|null $createdAt = null,
        public \DateTimeInterface|null $updatedAt = null,
        public \DateTimeInterface|null $publishedAt = null,
    ) {
    }

    public function getId(): string|null
    {
        return $this->id;
    }
}
