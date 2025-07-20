<?php

use App\Shared\Application\Gateway\Instrumentation\DefaultGatewayInstrumentation;
use App\Shared\Application\Gateway\Middleware\DefaultErrorHandler;
use App\Shared\Application\Gateway\Middleware\DefaultLogger;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use App\Shared\Application\Gateway\Attribute\AsGateway;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $gatewaysClasses = array_filter(get_declared_classes(), fn (string $class) => str_ends_with($class, 'Gateway'));

    foreach ($gatewaysClasses as $gatewayClass) {
        $reflectionClass = new ReflectionClass($gatewayClass);
        $gatewayAttributes = $reflectionClass->getAttributes(AsGateway::class);

        foreach ($gatewayAttributes as $gatewayAttribute) {
            /** @var AsGateway $gateway */
            $gateway = $gatewayAttribute->newInstance();

            $gatewayName = $gateway->context.'.'.$gateway->domain.'.'.$gateway->operation;
            $baseServiceName = $gatewayName.'.gateway';
            $instrumentationServiceName = $baseServiceName.'.instrumentation';

            $services
                ->set($instrumentationServiceName)
                ->class(DefaultGatewayInstrumentation::class)
                ->args(['$name' => $gatewayName])
            ;

            $middlewares = [];

            foreach ($gateway->middlewares as $middleware) {
                $middlewares[] = match ($middleware) {
                    DefaultErrorHandler::class => buildDefaultErrorHandlerMiddleware(
                        $services,
                        $baseServiceName.'.middleware.error_handler',
                        $instrumentationServiceName,
                        $gateway->context,
                        $gateway->domain,
                        $gateway->operation
                    ),
                    DefaultLogger::class => buildDefaultLoggerMiddleware(
                        $services,
                        $baseServiceName.'.middleware.logger',
                        $instrumentationServiceName
                    ),
                    default => $middleware,
                };
            }

            $services
                ->set($gatewayClass)
                ->class($gatewayClass)
                ->args([
                    array_map(fn (string $serviceName) => service($serviceName), $middlewares),
                ])
            ;
        }
    }
};

function buildDefaultErrorHandlerMiddleware(
    ServicesConfigurator $services,
    string $serviceName,
    string $instrumentationServiceName,
    string $contextName,
    string $domainName,
    string $operationName
): string {
    $services
        ->set($serviceName)
        ->class(DefaultErrorHandler::class)
        ->args([
            service($instrumentationServiceName),
            $contextName,
            $domainName,
            $operationName,
        ])
    ;

    return $serviceName;
}

function buildDefaultLoggerMiddleware(
    ServicesConfigurator $services,
    string $serviceName,
    string $instrumentationServiceName
): string {
    $services
        ->set($serviceName)
        ->class(DefaultLogger::class)
        ->args([service($instrumentationServiceName)])
    ;

    return $serviceName;
}
