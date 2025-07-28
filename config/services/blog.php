<?php

declare(strict_types=1);

use App\Blog\Application\Operation\Command\Author\CreateAuthor\Handler as CreateAuthorHandler;
use App\Blog\Application\Operation\Command\Author\CreateAuthor\HandlerInterface as CreateAuthorHandlerInterface;
use App\Blog\Domain\Author\AuthorCreator;
use App\Blog\Domain\Author\Shared\Repository\AuthorWriteRepositoryInterface;
use App\Blog\Domain\Author\Shared\Repository\AuthorReadRepositoryInterface;
use App\Blog\Domain\Category\Shared\Repository\CategoryWriteRepositoryInterface;
use App\Blog\Domain\Category\Shared\Repository\CategoryReadRepositoryInterface;
use App\Blog\Domain\Tag\Shared\Repository\TagWriteRepositoryInterface;
use App\Blog\Domain\Tag\Shared\Repository\TagReadRepositoryInterface;
use App\Blog\Application\Shared\Generator\AuthorIdGeneratorInterface;
use App\Blog\Infrastructure\Identity\AuthorIdGenerator;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\AuthorWriteRepository;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\AuthorReadRepository;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\CategoryWriteRepository;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\CategoryReadRepository;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\TagWriteRepository;
use App\Blog\Infrastructure\Persistence\Doctrine\ORM\TagReadRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    // Author services
    $services
        ->set(AuthorWriteRepositoryInterface::class)
        ->class(AuthorWriteRepository::class)
    ;

    $services
        ->set(AuthorReadRepositoryInterface::class)
        ->class(AuthorReadRepository::class)
    ;


    $services
        ->set(AuthorIdGeneratorInterface::class)
        ->class(AuthorIdGenerator::class)
    ;

    $services
        ->set(AuthorCreator::class)
    ;

    $services
        ->set(CreateAuthorHandlerInterface::class)
        ->class(CreateAuthorHandler::class)
    ;

    // Category services
    $services
        ->set(CategoryWriteRepositoryInterface::class)
        ->class(CategoryWriteRepository::class)
    ;

    $services
        ->set(CategoryReadRepositoryInterface::class)
        ->class(CategoryReadRepository::class)
    ;


    // Tag services
    $services
        ->set(TagWriteRepositoryInterface::class)
        ->class(TagWriteRepository::class)
    ;

    $services
        ->set(TagReadRepositoryInterface::class)
        ->class(TagReadRepository::class)
    ;

};