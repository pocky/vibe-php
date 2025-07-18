<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {

    $parameters = $containerConfigurator->parameters();
    $parameters->set('locale', 'en');

    $services = $containerConfigurator->services();

    # default configuration for services in *this* file
    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    # makes classes in src/ available to be used as services
    # this creates a service per class whose id is the fully-qualified class name
    $services
        ->load('App\\', __DIR__.'/../src/')
        ->exclude([
            __DIR__.'/../src/DependencyInjection',
            __DIR__.'/../src/Kernel.php',
            __DIR__.'/../src/Shared/Application/Gateway/Instrumentation/DefaultGatewayInstrumentation.php',
        ])
    ;

    # Configure DefaultGatewayInstrumentation manually since it needs a string parameter
    $services->set(\App\Shared\Application\Gateway\Instrumentation\DefaultGatewayInstrumentation::class)
        ->args([
            '$loggerInstrumentation' => service(\App\Shared\Infrastructure\Instrumentation\LoggerInstrumentation::class),
            '$name' => 'default.gateway',
        ]);

    # add more service definitions when explicit configuration is needed
    # please note that last definitions always *replace* previous ones
    $containerConfigurator->import('services/');
};
