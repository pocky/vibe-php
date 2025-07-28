<?php

declare(strict_types=1);

namespace App\Blog\UI\Web\Admin\Resource;

use App\Blog\UI\Web\Admin\Form\TagType;
use App\Blog\UI\Web\Admin\Grid\TagGrid;
use App\Blog\UI\Web\Admin\Processor\CreateTagProcessor;
use App\Blog\UI\Web\Admin\Processor\DeleteTagProcessor;
use App\Blog\UI\Web\Admin\Processor\UpdateTagProcessor;
use App\Blog\UI\Web\Admin\Provider\TagItemProvider;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Create;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Show;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;

#[AsResource(
    alias: 'app.tag',
    section: 'admin',
    formType: TagType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: 'doctrine/orm',
)]
#[Index(
    grid: TagGrid::class,
)]
#[Create(
    processor: CreateTagProcessor::class,
    redirectToRoute: 'app_admin_tag_index',
)]
#[Show(
    provider: TagItemProvider::class,
)]
#[Update(
    provider: TagItemProvider::class,
    processor: UpdateTagProcessor::class,
    redirectToRoute: 'app_admin_tag_index',
)]
#[Delete(
    provider: TagItemProvider::class,
    processor: DeleteTagProcessor::class,
)]
final class TagResource implements ResourceInterface
{
    public \DateTimeInterface|null $createdAt;

    public \DateTimeInterface|null $updatedAt;

    public function __construct(
        public string|null $id = null,
        public string|null $name = null,
        public string|null $slug = null,
        public int|null $articleCount = null,
        \DateTimeInterface|string|null $createdAt = null,
        \DateTimeInterface|string|null $updatedAt = null,
    ) {
        // Handle DateTime conversion from strings
        if (is_string($createdAt)) {
            try {
                $this->createdAt = new \DateTimeImmutable($createdAt);
            } catch (\Exception) {
                $this->createdAt = null;
            }
        } else {
            $this->createdAt = $createdAt;
        }

        if (is_string($updatedAt)) {
            try {
                $this->updatedAt = new \DateTimeImmutable($updatedAt);
            } catch (\Exception) {
                $this->updatedAt = null;
            }
        } else {
            $this->updatedAt = $updatedAt;
        }
    }

    public function getId(): string|null
    {
        return $this->id;
    }
}
