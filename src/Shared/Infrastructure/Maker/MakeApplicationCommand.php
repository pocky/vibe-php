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

final class MakeApplicationCommand extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:application:command';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new CQRS Command with Handler';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('context', InputArgument::REQUIRED, 'The context name (e.g., BlogContext)')
            ->addArgument('command-name', InputArgument::REQUIRED, 'The command name (e.g., CreateArticle)')
            ->setHelp($this->getCustomHelpFileContents('MakeApplicationCommand.txt'))
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $context = $input->getArgument('context');
        $commandName = $input->getArgument('command-name');

        // Clean up names
        $context = str_replace('\\', '', $context);
        $context = ltrim($context, 'App\\');
        if (str_ends_with($context, 'Context')) {
            $context = substr($context, 0, -7);
        }
        $context .= 'Context';

        $commandNamePascal = Str::asCamelCase($commandName);
        $commandNamePascal = ucfirst($commandNamePascal);

        // Extract entity from command name (e.g., CreateArticle -> Article)
        $entity = preg_replace('/^(Create|Update|Delete|Publish|Submit|Approve|Reject|Add)/', '', $commandNamePascal);
        $entitySnake = Str::asSnakeCase($entity);

        // Command namespace
        $commandNamespace = sprintf('%s\\Application\\Operation\\Command\\%s\\', $context, $commandNamePascal);

        // Generate Command class
        $commandClassDetails = $generator->createClassNameDetails(
            'Command',
            $commandNamespace
        );

        $generator->generateClass(
            $commandClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/command/Command.tpl.php',
            [
                'entity' => $entity,
                'entity_snake' => $entitySnake,
                'context' => $context,
            ]
        );

        // Generate Handler class
        $handlerClassDetails = $generator->createClassNameDetails(
            'Handler',
            $commandNamespace
        );

        $generator->generateClass(
            $handlerClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/command/Handler.tpl.php',
            [
                'command_name' => $commandNamePascal,
                'entity' => $entity,
                'entity_snake' => $entitySnake,
                'context' => $context,
                'use_case' => $commandNamePascal,
            ]
        );

        // Generate HandlerInterface
        $handlerInterfaceClassDetails = $generator->createClassNameDetails(
            'HandlerInterface',
            $commandNamespace
        );

        $generator->generateClass(
            $handlerInterfaceClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/command/HandlerInterface.tpl.php',
            []
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text([
            'Next steps:',
            sprintf(' - Define command properties in <info>%s</info>', $commandClassDetails->getFullName()),
            sprintf(' - Implement the handler logic in <info>%s</info>', $handlerClassDetails->getFullName()),
            sprintf(' - Create or update the domain Creator in <info>src/%s/Domain/%s/Creator.php</info>', $context, $commandNamePascal),
            ' - Register the handler as a message handler if using Messenger',
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
