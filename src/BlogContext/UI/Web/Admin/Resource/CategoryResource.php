<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Resource;

use App\BlogContext\UI\Web\Admin\Form\CategoryType;
use App\BlogContext\UI\Web\Admin\Grid\CategoryGrid;
use App\BlogContext\UI\Web\Admin\Processor\CreateCategoryProcessor;
// use App\BlogContext\UI\Web\Admin\Processor\DeleteCategoryProcessor;
// use App\BlogContext\UI\Web\Admin\Processor\UpdateCategoryProcessor;
use App\BlogContext\UI\Web\Admin\Provider\CategoryItemProvider;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Create;
// use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Show;
// use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;

#[AsResource(
    alias: 'app.category',
    section: 'admin',
    formType: CategoryType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: 'doctrine/orm',
)]
#[Index(
    grid: CategoryGrid::class,
)]
#[Create(
    processor: CreateCategoryProcessor::class,
    redirectToRoute: 'app_admin_category_index',
)]
#[Show(
    provider: CategoryItemProvider::class,
)]
// TODO: Enable when UpdateCategory Gateway is ready
// #[Update(
//     provider: CategoryItemProvider::class,
//     processor: UpdateCategoryProcessor::class,
//     redirectToRoute: 'app_admin_category_index',
// )]
// TODO: Enable when DeleteCategory Gateway is ready
// #[Delete(
//     provider: CategoryItemProvider::class,
//     processor: DeleteCategoryProcessor::class,
// )]
final class CategoryResource implements ResourceInterface
{
    public function __construct(
        public string|null $id = null,
        public string|null $name = null,
        public string|null $slug = null,
        public string|null $path = null,
        public string|null $parentId = null,
        public int $level = 1,
        public int $articleCount = 0,
        public string|null $description = null,
        public \DateTimeInterface|null $createdAt = null,
        public \DateTimeInterface|null $updatedAt = null,
    ) {
    }

    public function getId(): string|null
    {
        return $this->id;
    }
}
