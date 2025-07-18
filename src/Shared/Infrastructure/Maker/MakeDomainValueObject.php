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
use Symfony\Component\Console\Input\InputOption;

final class MakeDomainValueObject extends AbstractMaker
{
    private const array AVAILABLE_TEMPLATES = [
        'generic',
        'email',
        'money',
        'phone',
        'url',
        'percentage',
    ];

    public static function getCommandName(): string
    {
        return 'make:domain:value-object';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new Domain Value Object with validation';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('context', InputArgument::REQUIRED, 'The context name (e.g., BlogContext)')
            ->addArgument('name', InputArgument::REQUIRED, 'The value object name (e.g., Email, Status)')
            ->addOption('template', 't', InputOption::VALUE_OPTIONAL, sprintf('Template to use (%s)', implode(', ', self::AVAILABLE_TEMPLATES)), 'generic')
            ->setHelp($this->getCustomHelpFileContents('MakeDomainValueObject.txt'))
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $context = $input->getArgument('context');
        $name = $input->getArgument('name');
        $template = $input->getOption('template');

        // Validate template
        if (!in_array($template, self::AVAILABLE_TEMPLATES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid template "%s". Available templates: %s', $template, implode(', ', self::AVAILABLE_TEMPLATES)));
        }

        // Clean up names
        $context = str_replace('\\', '', $context);
        $context = ltrim($context, 'App\\');
        if (str_ends_with($context, 'Context')) {
            $context = substr($context, 0, -7);
        }
        $context .= 'Context';

        $namePascal = Str::asCamelCase($name);
        $namePascal = ucfirst($namePascal);
        $nameSnake = Str::asSnakeCase($name);

        // Value object namespace
        $valueObjectNamespace = sprintf('%s\\Domain\\Shared\\ValueObject\\', $context);

        // Generate Value Object class
        $valueObjectClassDetails = $generator->createClassNameDetails(
            $namePascal,
            $valueObjectNamespace
        );

        // Determine template file
        $templateFile = sprintf('ValueObject%s.tpl.php', ucfirst($template));

        $generator->generateClass(
            $valueObjectClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/valueObject/' . $templateFile,
            [
                'name' => $namePascal,
                'name_snake' => $nameSnake,
                'context' => $context,
                'validation_exception_namespace' => sprintf('App\\%s\\Domain\\Shared\\Exception', $context),
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text([
            'Next steps:',
            sprintf(' - Customize validation rules in <info>%s</info>', $valueObjectClassDetails->getFullName()),
            ' - Add translation keys for validation messages',
            ' - Consider adding factory methods if needed',
            ' - Add business-specific methods as required',
        ]);

        if ('generic' !== $template) {
            $io->text([
                '',
                sprintf('Template "%s" was used. You may need to:', $template),
                ' - Adjust validation rules for your specific requirements',
                ' - Update error message translation keys',
            ]);
        }
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
