<?php

declare(strict_types=1);

use App\Blog\UI\Web\Admin\Grid\TagGrid;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    // Grid services
    $services
        ->set(TagGrid::class)
        ->tag('sylius.grid', ['grid' => TagGrid::class])
    ;
};