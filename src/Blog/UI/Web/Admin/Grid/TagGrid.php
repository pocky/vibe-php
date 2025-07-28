<?php

declare(strict_types=1);

namespace App\Blog\UI\Web\Admin\Grid;

use App\Blog\UI\Web\Admin\Provider\TagGridProvider;
use App\Blog\UI\Web\Admin\Resource\TagResource;
use Sylius\Bundle\GridBundle\Builder\Action\CreateAction;
use Sylius\Bundle\GridBundle\Builder\Action\DeleteAction;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\MainActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\DateTimeField;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\Field\TwigField;
use Sylius\Bundle\GridBundle\Builder\Filter\DateFilter;
use Sylius\Bundle\GridBundle\Builder\Filter\StringFilter;
use Sylius\Bundle\GridBundle\Builder\GridBuilderInterface;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;

final class TagGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(TagGridProvider::class)
            ->setLimits([10, 20, 50])
            ->addField(
                StringField::create('name')
                    ->setLabel('app.ui.name')
                    ->setSortable(true)
            )
            ->addField(
                StringField::create('slug')
                    ->setLabel('app.ui.slug')
                    ->setSortable(true)
            )
            ->addField(
                TwigField::create('articleCount', '@Blog/admin/tag/grid/field/article_count.html.twig')
                    ->setLabel('app.ui.article_count')
                    ->setSortable(true)
            )
            ->addField(
                DateTimeField::create('createdAt')
                    ->setLabel('app.ui.created_at')
                    ->setSortable(true)
            )
            ->addFilter(
                StringFilter::create('name')
                    ->setLabel('app.ui.name')
            )
            ->addFilter(
                StringFilter::create('slug')
                    ->setLabel('app.ui.slug')
            )
            ->addFilter(
                DateFilter::create('createdAt')
                    ->setLabel('app.ui.created_at')
            )
            ->addActionGroup(
                MainActionGroup::create(
                    CreateAction::create()
                        ->setLabel('app.ui.create_tag')
                )
            )
            ->addActionGroup(
                ItemActionGroup::create(
                    UpdateAction::create(),
                    DeleteAction::create()
                )
            );
    }

    public function getResourceClass(): string
    {
        return TagResource::class;
    }
}
