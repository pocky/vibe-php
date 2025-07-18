<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Grid;

use App\BlogContext\UI\Web\Admin\Provider\CategoryGridProvider;
use App\BlogContext\UI\Web\Admin\Resource\CategoryResource;
use Sylius\Bundle\GridBundle\Builder\Action\CreateAction;
use Sylius\Bundle\GridBundle\Builder\Action\DeleteAction;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\MainActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\DateTimeField;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\GridBuilderInterface;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;

final class CategoryGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(CategoryGridProvider::class)
            ->setLimits([10, 20, 50])
            ->addField(StringField::create('name')->setLabel('Name'))
            ->addField(StringField::create('slug')->setLabel('Slug'))
            ->addField(StringField::create('path')->setLabel('Path'))
            ->addField(StringField::create('level')->setLabel('Level'))
            ->addField(StringField::create('articleCount')->setLabel('Articles'))
            ->addField(DateTimeField::create('createdAt')->setLabel('Created At'))
            ->addActionGroup(
                MainActionGroup::create(
                    CreateAction::create()
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
        return CategoryResource::class;
    }
}
