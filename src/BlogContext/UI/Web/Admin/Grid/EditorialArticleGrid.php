<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Grid;

use App\BlogContext\UI\Web\Admin\Provider\EditorialArticleGridProvider;
use App\BlogContext\UI\Web\Admin\Resource\EditorialArticleResource;
use Sylius\Bundle\GridBundle\Builder\Action\Action;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\BulkActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\DateTimeField;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\Filter\StringFilter;
use Sylius\Bundle\GridBundle\Builder\GridBuilderInterface;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;

final class EditorialArticleGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(EditorialArticleGridProvider::class)
            ->addField(StringField::create('title')->setLabel('Title'))
            ->addField(StringField::create('authorName')->setLabel('Author'))
            ->addField(DateTimeField::create('submittedAt')->setLabel('Submitted At'))
            ->addField(StringField::create('status')->setLabel('Status'))
            ->addFilter(StringFilter::create('status', ['pending_review']))
            ->addActionGroup(
                ItemActionGroup::create(
                    Action::create('show', 'show')
                        ->setLabel('Review')
                        ->setIcon('eye'),
                    Action::create('approve', 'approve')
                        ->setLabel('Approve')
                        ->setIcon('check')
                        ->setOptions([
                            'method' => 'POST',
                        ]),
                    Action::create('reject', 'reject')
                        ->setLabel('Reject')
                        ->setIcon('times')
                        ->setOptions([
                            'method' => 'POST',
                        ])
                )
            )
            ->addActionGroup(
                BulkActionGroup::create(
                    Action::create('bulk_approve', 'bulk_action')
                        ->setLabel('Approve Selected')
                        ->setOptions([
                            'bulk_action' => 'approve',
                        ]),
                    Action::create('bulk_reject', 'bulk_action')
                        ->setLabel('Reject Selected')
                        ->setOptions([
                            'bulk_action' => 'reject',
                        ])
                )
            );
    }

    public function getResourceClass(): string
    {
        return EditorialArticleResource::class;
    }
}
