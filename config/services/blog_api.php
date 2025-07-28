<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // Auto-configure API providers and processors
    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->private();

    // Tag API providers
    $services->load('App\\Blog\\UI\\Api\\Rest\\Provider\\', '../../src/Blog/UI/Api/Rest/Provider/');
    
    // Tag API processors
    $services->load('App\\Blog\\UI\\Api\\Rest\\Processor\\', '../../src/Blog/UI/Api/Rest/Processor/');
};