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

final class MakeDomainIdGenerator extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:domain:id-generator';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a domain ID generator for specific entity identity generation';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfiguration): void
    {
        $command
            ->addArgument('context', InputArgument::REQUIRED, 'The context name (e.g., BlogContext)')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity name (e.g., Article)')
            ->setHelp($this->getCustomHelpFileContents('MakeDomainIdGenerator.txt'));
    }

    public function generate(InputInterface $input, ConsoleStyle $consoleStyle, Generator $generator): void
    {
        $context = $input->getArgument('context');
        $entity = $input->getArgument('entity');

        // Clean up context name (remove Context suffix if present, App prefix)
        $context = str_replace('\\', '', $context);
        $context = ltrim($context, 'App\\');
        if (str_ends_with($context, 'Context')) {
            $context = substr($context, 0, -7);
        }

        $context .= 'Context';

        // Prepare entity names
        $entityPascalCase = Str::asCamelCase($entity);
        $entityPascalCase = ucfirst($entityPascalCase);

        // Create ID class details (must exist) - new structure
        $classNameDetails = $generator->createClassNameDetails(
            $entityPascalCase . 'Id',
            sprintf('%s\\Domain\\%s\\Shared\\ValueObject\\', $context, $entityPascalCase)
        );

        // Check if ID class exists - new structure
        $idClassPath = sprintf(
            'src/%s/Domain/%s/Shared/ValueObject/%sId.php',
            $context,
            $entityPascalCase,
            $entityPascalCase
        );

        if (!file_exists($idClassPath)) {
            $consoleStyle->error(sprintf(
                'The %sId value object does not exist yet. Please create it first with:',
                $entityPascalCase
            ));
            $consoleStyle->text(sprintf('  php bin/console make:domain:value-object %s %sId --entity=%s', $context, $entityPascalCase, $entityPascalCase));

            return;
        }

        // Generate the ID generator
        $idGeneratorDetails = $generator->createClassNameDetails(
            $entityPascalCase . 'IdGenerator',
            sprintf('%s\\Infrastructure\\Identity\\', $context)
        );

        $generator->generateClass(
            $idGeneratorDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/infrastructure/IdGenerator.tpl.php',
            [
                'id_class_full_name' => $classNameDetails->getFullName(),
                'id_class_short_name' => $classNameDetails->getShortName(),
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($consoleStyle);

        $consoleStyle->text([
            'Next steps:',
            sprintf(' - Use the generator in your domain services: <info>%s</info>', $idGeneratorDetails->getFullName()),
            sprintf(' - Inject it via constructor: <info>private %s $idGenerator</info>', $idGeneratorDetails->getShortName()),
            sprintf(' - Generate IDs: <info>$%sId = $this->idGenerator->nextIdentity();</info>', lcfirst($entityPascalCase)),
            '',
            'Example usage in a Creator:',
            sprintf('  private %s $idGenerator', $idGeneratorDetails->getShortName()),
            sprintf('  $%s = $this->idGenerator->nextIdentity();', lcfirst($entityPascalCase) . 'Id'),
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencyBuilder): void
    {
        // No additional dependencies needed
    }

    private function getCustomHelpFileContents(string $fileName): string
    {
        return file_get_contents(__DIR__ . '/Resources/help/' . $fileName);
    }
}
