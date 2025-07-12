<?php

declare(strict_types=1);

use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\ArticleRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // BlogContext Infrastructure Services
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Repository Interface Binding
    $services->alias(ArticleRepositoryInterface::class, ArticleRepository::class);

    // Domain Services Auto-registration
    $services->load('App\\BlogContext\\Domain\\', '../../src/BlogContext/Domain/')
        ->exclude([
            '../../src/BlogContext/Domain/*/DataPersister/',
            '../../src/BlogContext/Domain/*/DataProvider/',
            '../../src/BlogContext/Domain/*/Event/',
            '../../src/BlogContext/Domain/*/Exception/',
            '../../src/BlogContext/Domain/Shared/ValueObject/',
        ]);

    // Application Services Auto-registration
    $services->load('App\\BlogContext\\Application\\', '../../src/BlogContext/Application/');

    // Infrastructure Services Auto-registration
    $services->load('App\\BlogContext\\Infrastructure\\', '../../src/BlogContext/Infrastructure/')
        ->exclude([
            '../../src/BlogContext/Infrastructure/Persistence/Doctrine/ORM/Entity/',
        ]);
};
