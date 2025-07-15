<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Symfony\CompilerPass;

use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Mapper\ArticleMappingRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register article mappers in the registry
 */
final class ArticleMapperPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ArticleMappingRegistry::class)) {
            return;
        }

        $registryDefinition = $container->findDefinition(ArticleMappingRegistry::class);
        $taggedServices = $container->findTaggedServiceIds('blog.article_mapper');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                /** @var array{article_class?: string} $attributes */
                if (!isset($attributes['article_class'])) {
                    throw new \RuntimeException(sprintf('The service "%s" tagged with "blog.article_mapper" must have an "article_class" attribute.', $id));
                }

                $registryDefinition->addMethodCall(
                    'register',
                    [$attributes['article_class'], new Reference($id)]
                );
            }
        }
    }
}
