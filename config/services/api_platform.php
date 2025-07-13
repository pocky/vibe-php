<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // API Platform State Providers
    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->private();

    // Auto-register State Providers when they exist
    $services->defaults()
        ->tag('api_platform.state_provider');

    // Auto-register State Processors when they exist  
    $services->defaults()
        ->tag('api_platform.state_processor');

    // Auto-register API Filters when they exist
    $services->defaults()
        ->tag('api_platform.filter');

    // Auto-register API Resources when they exist
    $services->defaults()
        ->tag('api_platform.resource');
};