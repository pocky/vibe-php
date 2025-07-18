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

final class MakeAdminResource extends AbstractMaker
{
    private readonly Inflector $inflector;

    public function __construct()
    {
        $this->inflector = InflectorFactory::create()->build();
    }

    public static function getCommandName(): string
    {
        return 'make:admin:resource';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a complete Sylius Admin resource with Grid, Form, Providers and Processors';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('context', InputArgument::REQUIRED, 'The context name (e.g., BlogContext)')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity name (e.g., Category)')
            ->setHelp($this->getCustomHelpFileContents('MakeAdminResource.txt'))
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

        // Admin namespace
        $adminNamespace = sprintf('%s\\UI\\Web\\Admin\\', $context);

        // Generate Resource class
        $resourceClassDetails = $generator->createClassNameDetails(
            $entityPascal . 'Resource',
            $adminNamespace . 'Resource\\'
        );

        $generator->generateClass(
            $resourceClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/admin/Resource.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'entity_camel' => $entityCamel,
                'context' => $context,
                'context_snake' => Str::asSnakeCase(str_replace('Context', '', $context)),
            ]
        );

        // Generate Grid class
        $gridClassDetails = $generator->createClassNameDetails(
            $entityPascal . 'Grid',
            $adminNamespace . 'Grid\\'
        );

        $generator->generateClass(
            $gridClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/admin/Grid.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'context' => $context,
            ]
        );

        // Generate Form Type
        $formClassDetails = $generator->createClassNameDetails(
            $entityPascal . 'Type',
            $adminNamespace . 'Form\\'
        );

        $generator->generateClass(
            $formClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/admin/FormType.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'entity_camel' => $entityCamel,
                'context' => $context,
            ]
        );

        // Generate Grid Provider
        $gridProviderClassDetails = $generator->createClassNameDetails(
            $entityPascal . 'GridProvider',
            $adminNamespace . 'Provider\\'
        );

        $generator->generateClass(
            $gridProviderClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/admin/GridProvider.tpl.php',
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

        // Generate Item Provider
        $itemProviderClassDetails = $generator->createClassNameDetails(
            $entityPascal . 'ItemProvider',
            $adminNamespace . 'Provider\\'
        );

        $generator->generateClass(
            $itemProviderClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/admin/ItemProvider.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'entity_camel' => $entityCamel,
                'context' => $context,
            ]
        );

        // Generate Create Processor
        $createProcessorClassDetails = $generator->createClassNameDetails(
            'Create' . $entityPascal . 'Processor',
            $adminNamespace . 'Processor\\'
        );

        $generator->generateClass(
            $createProcessorClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/admin/CreateProcessor.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'entity_camel' => $entityCamel,
                'context' => $context,
            ]
        );

        // Generate Update Processor
        $updateProcessorClassDetails = $generator->createClassNameDetails(
            'Update' . $entityPascal . 'Processor',
            $adminNamespace . 'Processor\\'
        );

        $generator->generateClass(
            $updateProcessorClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/admin/UpdateProcessor.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'entity_camel' => $entityCamel,
                'context' => $context,
            ]
        );

        // Generate Delete Processor
        $deleteProcessorClassDetails = $generator->createClassNameDetails(
            'Delete' . $entityPascal . 'Processor',
            $adminNamespace . 'Processor\\'
        );

        $generator->generateClass(
            $deleteProcessorClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/admin/DeleteProcessor.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'entity_camel' => $entityCamel,
                'context' => $context,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text([
            'Next steps:',
            sprintf(' - Customize the form in <info>%s</info>', $formClassDetails->getFullName()),
            sprintf(' - Add fields to the grid in <info>%s</info>', $gridClassDetails->getFullName()),
            sprintf(' - Update resource properties in <info>%s</info>', $resourceClassDetails->getFullName()),
            ' - Create corresponding Gateway operations if needed',
            ' - Add translations for labels and messages',
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
