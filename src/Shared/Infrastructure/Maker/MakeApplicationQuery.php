<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

final class MakeApplicationQuery extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:application:query';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new CQRS Query with Handler';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('context', InputArgument::REQUIRED, 'The context name (e.g., BlogContext)')
            ->addArgument('query-name', InputArgument::REQUIRED, 'The query name (e.g., GetArticle, ListArticles)')
            ->setHelp($this->getCustomHelpFileContents('MakeApplicationQuery.txt'))
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $context = $input->getArgument('context');
        $queryName = $input->getArgument('query-name');

        // Clean up names
        $context = str_replace('\\', '', $context);
        $context = ltrim($context, 'App\\');
        if (str_ends_with($context, 'Context')) {
            $context = substr($context, 0, -7);
        }
        $context .= 'Context';

        $queryNamePascal = Str::asCamelCase($queryName);
        $queryNamePascal = ucfirst($queryNamePascal);

        // Extract entity from query name (e.g., GetArticle -> Article, ListArticles -> Article)
        $entity = preg_replace('/^(Get|List|Search|Find)/', '', $queryNamePascal);
        $entity = preg_replace('/s$/', '', (string) $entity); // Remove plural
        $entitySnake = Str::asSnakeCase($entity);

        // Determine if it's a collection query
        $isCollection = str_starts_with($queryNamePascal, 'List') || str_starts_with($queryNamePascal, 'Search');

        // Query namespace
        $queryNamespace = sprintf('%s\\Application\\Operation\\Query\\%s\\', $context, $queryNamePascal);

        // Generate Query class
        $queryClassDetails = $generator->createClassNameDetails(
            'Query',
            $queryNamespace
        );

        $generator->generateClass(
            $queryClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/query/Query.tpl.php',
            [
                'is_collection' => $isCollection,
                'entity' => $entity,
                'entity_snake' => $entitySnake,
            ]
        );

        // Generate Handler class
        $handlerClassDetails = $generator->createClassNameDetails(
            'Handler',
            $queryNamespace
        );

        $generator->generateClass(
            $handlerClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/query/Handler.tpl.php',
            [
                'query_name' => $queryNamePascal,
                'entity' => $entity,
                'entity_snake' => $entitySnake,
                'context' => $context,
                'is_collection' => $isCollection,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text([
            'Next steps:',
            sprintf(' - Define query parameters in <info>%s</info>', $queryClassDetails->getFullName()),
            sprintf(' - Implement the handler logic in <info>%s</info>', $handlerClassDetails->getFullName()),
            ' - Register the handler as a message handler if using Messenger',
            ' - Consider creating a View model if you need a different response structure',
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // No additional dependencies needed
    }

    private function getCustomHelpFileContents(string $fileName): string
    {
        return file_get_contents(__DIR__ . '/Resources/help/' . $fileName);
    }
}
