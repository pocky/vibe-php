<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // This configuration is kept minimal for any manual overrides if needed

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    // Load all classes in the Maker namespace
    // The CompilerPass will automatically tag the actual Maker classes
    $services
        ->load('App\\Shared\\Infrastructure\\Maker\\', '../../src/Shared/Infrastructure/Maker/')
        ->exclude([
            '../../src/Shared/Infrastructure/Maker/Resources/',
        ])
    ;
};
