<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Sylius\AdminUi\Knp\Menu\MenuBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: 'sylius_admin_ui.knp.menu_builder')]
final readonly class MenuBuilder implements MenuBuilderInterface
{
    public function __construct(
        private FactoryInterface $factory,
    ) {
    }

    public function createMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu
            ->addChild('dashboard', [
                'route' => 'sylius_admin_ui_dashboard',
            ])
            ->setLabel('sylius.ui.dashboard')
            ->setLabelAttribute('icon', 'tabler:dashboard')
        ;

        $this->addContentSubMenu($menu);

        return $menu;
    }

    private function addContentSubMenu(ItemInterface $menu): void
    {
        $content = $menu
            ->addChild('content')
            ->setLabel('app.ui.content')
            ->setLabelAttribute('icon', 'tabler:file-text')
        ;

        $content->addChild('articles', [
            'route' => 'app_admin_article_index',
        ])
            ->setLabel('app.ui.articles')
            ->setLabelAttribute('icon', 'tabler:article')
        ;

        $content->addChild('editorial', [
            'route' => 'app_admin_editorial_index',
        ])
            ->setLabel('app.ui.editorial_articles')
            ->setLabelAttribute('icon', 'tabler:article')
        ;
    }
}
