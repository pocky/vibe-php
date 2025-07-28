<?php

declare(strict_types=1);

use App\Blog\UI\Web\Admin\Menu\AdminMenuBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    // Menu builders
    $services
        ->set(AdminMenuBuilder::class)
        ->tag('sylius_admin_ui.knp.menu_builder')
    ;

    // Providers
    $services->load('App\\Blog\\UI\\Web\\Admin\\Provider\\', '../../src/Blog/UI/Web/Admin/Provider/*');

    // Processors
    $services->load('App\\Blog\\UI\\Web\\Admin\\Processor\\', '../../src/Blog/UI/Web/Admin/Processor/*');

    // Forms
    $services->load('App\\Blog\\UI\\Web\\Admin\\Form\\', '../../src/Blog/UI/Web/Admin/Form/*');
};