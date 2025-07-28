<?php

declare(strict_types=1);

namespace App\Blog\UI\Web\Admin\Menu;

use Knp\Menu\ItemInterface;
use Sylius\AdminUi\Knp\Menu\MenuBuilderInterface;

final class AdminMenuBuilder implements MenuBuilderInterface
{
    public function createMenu(array $options = []): ItemInterface
    {
        throw new \LogicException('This method should not be called.');
    }

    public function build(ItemInterface $item): void
    {
        $blog = $item
            ->addChild('blog', [
                'label' => 'app.ui.blog',
            ])
            ->setLabel('app.ui.blog')
            ->setLabelAttribute('icon', 'newspaper');

        $blog
            ->addChild('tags', [
                'route' => 'app_admin_tag_index',
                'label' => 'app.ui.tags',
            ])
            ->setLabel('app.ui.tags')
            ->setLabelAttribute('icon', 'tags');
    }
}
