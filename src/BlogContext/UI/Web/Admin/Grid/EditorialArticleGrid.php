<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Grid;

use App\BlogContext\UI\Web\Admin\Provider\EditorialArticleGridProvider;
use App\BlogContext\UI\Web\Admin\Resource\EditorialArticleResource;
use Sylius\Bundle\GridBundle\Builder\Action\Action;
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
            ->setLimits([10, 20, 50])
            ->addField(StringField::create('title')->setLabel('Title')->setEnabled(true))
            ->addField(StringField::create('authorName')->setLabel('Author')->setEnabled(true))
            ->addField(DateTimeField::create('submittedAt')->setLabel('Submitted At')->setEnabled(true))
            ->addField(StringField::create('status')->setLabel('Status')->setEnabled(true))
            ->addFilter(StringFilter::create('status', ['pending_review']))
            ->addActionGroup(
                ItemActionGroup::create(
                    Action::create('review', 'show')
                        ->setLabel('Review')
                        ->setIcon('tabler:eye'),
                    Action::create('approve', 'approve')
                        ->setLabel('Approve')
                        ->setIcon('tabler:check')
                        ->setOptions([
                            'link' => [
                                'route' => 'app_admin_editorial_approve',
                                'parameters' => [
                                    'id' => 'resource.id',
                                ],
                            ],
                        ]),
                    Action::create('reject', 'reject')
                        ->setLabel('Reject')
                        ->setIcon('tabler:x')
                        ->setOptions([
                            'link' => [
                                'route' => 'app_admin_editorial_reject',
                                'parameters' => [
                                    'id' => 'resource.id',
                                ],
                            ],
                        ])
                )
            );
        // Bulk actions temporarily disabled due to template issue
        // TODO: Implement custom bulk action templates when needed
    }

    public function getResourceClass(): string
    {
        return EditorialArticleResource::class;
    }
}
