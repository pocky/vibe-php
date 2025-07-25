<?php

declare(strict_types=1);

use App\BlogContext\Domain\Shared\Generator\AuthorIdGeneratorInterface;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Infrastructure\Identity\AuthorIdGenerator;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\AuthorRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    // Author services
    $services
        ->set(AuthorRepositoryInterface::class)
        ->class(AuthorRepository::class)
    ;

    $services
        ->set(AuthorIdGeneratorInterface::class)
        ->class(AuthorIdGenerator::class)
    ;
};