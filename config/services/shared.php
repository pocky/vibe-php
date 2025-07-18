<?php

declare(strict_types=1);

use App\Shared\Application\Gateway\Instrumentation\DefaultGatewayInstrumentation;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // Shared Infrastructure Services
    $services
        ->defaults()
        ->autowire()
        ->autoconfigure();

    // Configure DefaultGatewayInstrumentation with default name
    $services->set(DefaultGatewayInstrumentation::class)
        ->args([
            '$loggerInstrumentation' => '@App\Shared\Infrastructure\Instrumentation\LoggerInstrumentation',
            '$name' => 'default.gateway',
        ]);
};
