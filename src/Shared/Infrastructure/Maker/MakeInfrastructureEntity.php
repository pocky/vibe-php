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

final class MakeInfrastructureEntity extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:infrastructure:entity';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a Doctrine entity in DDD infrastructure layer';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('context', InputArgument::REQUIRED, 'The context name (e.g. BlogContext)')
            ->addArgument('name', InputArgument::REQUIRED, sprintf('Entity name (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->setHelp($this->getCustomHelpFileContents('MakeInfrastructureEntity.txt'));
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $context = $input->getArgument('context');
        $entityClassName = $input->getArgument('name');

        // Handle context input - remove App\ prefix if present
        $context = ltrim((string) $context, 'App\\');
        $context = str_replace('\\', '', $context);

        // Generate namespace from context - without App prefix since Generator adds it
        $entityNamespace = sprintf('%s\\Infrastructure\\Persistence\\Doctrine\\ORM\\Entity\\', $context);
        $repoNamespace = sprintf('%s\\Infrastructure\\Persistence\\Doctrine\\ORM\\', $context);

        // Create the entity class details
        $entityClassDetails = $generator->createClassNameDetails(
            $entityClassName,
            $entityNamespace
        );

        // Create the repository class details
        $repoClassDetails = $generator->createClassNameDetails(
            $entityClassName,
            $repoNamespace,
            'Repository'
        );

        // Prepare the table name (e.g. blog_articles for Article in BlogContext)
        $contextPrefix = Str::asSnakeCase(str_replace('Context', '', $context));
        $entitySnakeCase = Str::asSnakeCase($entityClassName);
        // Simple pluralization
        if (str_ends_with($entitySnakeCase, 'y') && !in_array(substr($entitySnakeCase, -2), ['ay', 'ey', 'iy', 'oy', 'uy'])) {
            $pluralEntity = substr($entitySnakeCase, 0, -1) . 'ies';
        } else {
            $pluralEntity = $entitySnakeCase . 's';
        }
        $tableName = sprintf('%s_%s', $contextPrefix, $pluralEntity);

        // Generate the value object ID
        $idClassDetails = $generator->createClassNameDetails(
            $entityClassName . 'Id',
            sprintf('%s\\Domain\\Shared\\ValueObject\\', $context)
        );

        $generator->generateClass(
            $idClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/domain/EntityId.tpl.php',
            [
                'entity_snake_case' => $entitySnakeCase,
                'validation_exception_namespace' => sprintf('App\\%s\\Domain\\Shared\\Exception', $context),
            ]
        );

        // Generate the repository interface
        $repoInterfaceDetails = $generator->createClassNameDetails(
            $entityClassName . 'RepositoryInterface',
            sprintf('%s\\Domain\\Shared\\Repository\\', $context)
        );

        $generator->generateClass(
            $repoInterfaceDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/domain/RepositoryInterface.tpl.php',
            [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_variable' => lcfirst($entityClassDetails->getShortName()),
                'context' => $context,
            ]
        );

        // Generate the entity file
        $entityPath = $generator->generateClass(
            $entityClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/doctrine/Entity.tpl.php',
            [
                'repository_class_name' => $repoClassDetails->getShortName(),
                'repository_full_class_name' => $repoClassDetails->getFullName(),
                'table_name' => $tableName,
                'use_uuid' => true,
            ]
        );

        // Generate the repository file
        $repoPath = $generator->generateClass(
            $repoClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/doctrine/DomainRepository.tpl.php',
            [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_variable' => lcfirst($entityClassDetails->getShortName()),
                'context' => $context,
            ]
        );

        // Generate the ID generator
        $idGeneratorDetails = $generator->createClassNameDetails(
            $entityClassName . 'IdGenerator',
            sprintf('%s\\Infrastructure\\Identity\\', $context)
        );

        $generator->generateClass(
            $idGeneratorDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/infrastructure/IdGenerator.tpl.php',
            [
                'id_class_full_name' => $idClassDetails->getFullName(),
                'id_class_short_name' => $idClassDetails->getShortName(),
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text([
            'Next: Add fields to your entity and generate a migration:',
            '',
            sprintf('  <info>php %s make:entity --regenerate %s</info>', $_SERVER['PHP_SELF'] ?? 'bin/console', $entityClassDetails->getFullName()),
            sprintf('  <info>php %s make:migration</info>', $_SERVER['PHP_SELF'] ?? 'bin/console'),
            '',
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // Dependencies are already satisfied in our project
    }

    private function getCustomHelpFileContents(string $fileName): string
    {
        return file_get_contents(__DIR__ . '/Resources/help/' . $fileName);
    }
}
