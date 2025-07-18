<?php

declare(strict_types=1);

use App\BlogContext\Application\Gateway\PublishArticle\Constraint\SeoReadyValidator;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\ArticleRepository;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper\ArticleMappingRegistry;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper\CreateArticleMapper;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper\PublishArticleMapper;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper\ReviewedArticleMapper;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper\SubmitForReviewMapper;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper\UpdateArticleMapper;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // BlogContext Infrastructure Services
    $services
        ->defaults()
        ->autowire()
        ->autoconfigure();

    // Repository Interface Binding
    $services->alias(ArticleRepositoryInterface::class, ArticleRepository::class);

    // Article Mapping Infrastructure
    $services->set(ArticleMappingRegistry::class)
        ->public();

    // Article Mappers
    $services->set(CreateArticleMapper::class)
        ->tag('blog.article_mapper', ['article_class' => \App\BlogContext\Domain\CreateArticle\DataPersister\Article::class]);

    $services->set(UpdateArticleMapper::class)
        ->tag('blog.article_mapper', ['article_class' => \App\BlogContext\Domain\UpdateArticle\DataPersister\Article::class]);

    $services->set(PublishArticleMapper::class)
        ->tag('blog.article_mapper', ['article_class' => \App\BlogContext\Domain\PublishArticle\DataPersister\Article::class]);

    $services->set(SubmitForReviewMapper::class)
        ->tag('blog.article_mapper', ['article_class' => \App\BlogContext\Domain\SubmitForReview\DataPersister\Article::class]);

    $services->set(ReviewedArticleMapper::class)
        ->tag('blog.article_mapper', ['article_class' => \App\BlogContext\Domain\ReviewArticle\DataPersister\ReviewedArticle::class]);

    // Entity to domain mapper
    $services->set(\App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper\EntityToArticleMapper::class);

    // Repository with mapping registry
    $services->set(ArticleRepository::class)
        ->arg('$entityManager', service('doctrine.orm.entity_manager'))
        ->arg('$mappingRegistry', service(ArticleMappingRegistry::class))
        ->arg('$entityToArticleMapper', service(\App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper\EntityToArticleMapper::class));

    // SeoReadyValidator configuration
    $services->set(SeoReadyValidator::class)
        ->autowire()
        ->autoconfigure()
        ->tag('validator.constraint_validator');
};
