<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Maker;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

final class MakeApiResource extends AbstractMaker
{
    private readonly Inflector $inflector;

    public function __construct()
    {
        $this->inflector = InflectorFactory::create()->build();
    }

    public static function getCommandName(): string
    {
        return 'make:api:resource';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new API Platform resource with Providers and Processors';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('context', InputArgument::REQUIRED, 'The context name (e.g., BlogContext)')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity name (e.g., Article)')
            ->setHelp($this->getCustomHelpFileContents('MakeApiResource.txt'))
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $context = $input->getArgument('context');
        $entity = $input->getArgument('entity');

        // Clean up names
        $context = str_replace('\\', '', $context);
        $context = ltrim($context, 'App\\');
        if (str_ends_with($context, 'Context')) {
            $context = substr($context, 0, -7);
        }
        $context .= 'Context';

        $entityPascal = Str::asCamelCase($entity);
        $entityPascal = ucfirst($entityPascal);
        $entitySnake = Str::asSnakeCase($entity);
        $entityCamel = lcfirst($entityPascal);

        // Generate plural forms using Doctrine Inflector
        $entityPluralPascal = ucfirst($this->inflector->pluralize($entityPascal));
        $entityPluralCamel = lcfirst($entityPluralPascal);
        $entityPluralSnake = Str::asSnakeCase($entityPluralPascal);

        // API namespace
        $apiNamespace = sprintf('%s\\UI\\Api\\Rest\\', $context);

        // Generate Resource class
        $resourceClassDetails = $generator->createClassNameDetails(
            $entityPascal . 'Resource',
            $apiNamespace . 'Resource\\'
        );

        $generator->generateClass(
            $resourceClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/api/Resource.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'entity_camel' => $entityCamel,
                'entity_plural_pascal' => $entityPluralPascal,
                'entity_plural_camel' => $entityPluralCamel,
                'entity_plural_snake' => $entityPluralSnake,
                'context' => $context,
            ]
        );

        // Generate Get Provider
        $getProviderClassDetails = $generator->createClassNameDetails(
            'Get' . $entityPascal . 'Provider',
            $apiNamespace . 'Provider\\'
        );

        $generator->generateClass(
            $getProviderClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/api/GetProvider.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'entity_camel' => $entityCamel,
                'entity_plural_pascal' => $entityPluralPascal,
                'entity_plural_camel' => $entityPluralCamel,
                'entity_plural_snake' => $entityPluralSnake,
                'context' => $context,
            ]
        );

        // Generate List Provider
        $listProviderClassDetails = $generator->createClassNameDetails(
            'List' . $entityPluralPascal . 'Provider',
            $apiNamespace . 'Provider\\'
        );

        $generator->generateClass(
            $listProviderClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/api/ListProvider.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'entity_camel' => $entityCamel,
                'entity_plural' => $entityPluralCamel,
                'entity_plural_pascal' => $entityPluralPascal,
                'entity_plural_camel' => $entityPluralCamel,
                'entity_plural_snake' => $entityPluralSnake,
                'context' => $context,
            ]
        );

        // Generate Create Processor
        $createProcessorClassDetails = $generator->createClassNameDetails(
            'Create' . $entityPascal . 'Processor',
            $apiNamespace . 'Processor\\'
        );

        $generator->generateClass(
            $createProcessorClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/api/CreateProcessor.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'entity_camel' => $entityCamel,
                'entity_plural_pascal' => $entityPluralPascal,
                'entity_plural_camel' => $entityPluralCamel,
                'entity_plural_snake' => $entityPluralSnake,
                'context' => $context,
            ]
        );

        // Generate Update Processor
        $updateProcessorClassDetails = $generator->createClassNameDetails(
            'Update' . $entityPascal . 'Processor',
            $apiNamespace . 'Processor\\'
        );

        $generator->generateClass(
            $updateProcessorClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/api/UpdateProcessor.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'entity_camel' => $entityCamel,
                'entity_plural_pascal' => $entityPluralPascal,
                'entity_plural_camel' => $entityPluralCamel,
                'entity_plural_snake' => $entityPluralSnake,
                'context' => $context,
            ]
        );

        // Generate Delete Processor
        $deleteProcessorClassDetails = $generator->createClassNameDetails(
            'Delete' . $entityPascal . 'Processor',
            $apiNamespace . 'Processor\\'
        );

        $generator->generateClass(
            $deleteProcessorClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/api/DeleteProcessor.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'entity_camel' => $entityCamel,
                'entity_plural_pascal' => $entityPluralPascal,
                'entity_plural_camel' => $entityPluralCamel,
                'entity_plural_snake' => $entityPluralSnake,
                'context' => $context,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text([
            'Next steps:',
            sprintf(' - Customize the resource properties in <info>%s</info>', $resourceClassDetails->getFullName()),
            ' - Add validation constraints to the resource class',
            ' - Configure filters if needed',
            ' - Create corresponding Gateway operations if they don\'t exist',
            ' - Add OpenAPI documentation annotations',
            ' - Test your API endpoints',
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
