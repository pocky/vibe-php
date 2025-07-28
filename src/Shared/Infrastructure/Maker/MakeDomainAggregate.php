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

final class MakeDomainAggregate extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:domain:aggregate';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new Domain Aggregate with Creator, Model, and Event';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfiguration): void
    {
        $command
            ->addArgument('context', InputArgument::REQUIRED, 'The context name (e.g., Blog)')
            ->addArgument('use-case', InputArgument::REQUIRED, 'The use case name (e.g., CreateArticle)')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity name (e.g., Article)')
            ->setHelp($this->getCustomHelpFileContents('MakeDomainAggregate.txt'))
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $consoleStyle, Generator $generator): void
    {
        $context = $input->getArgument('context');
        $useCase = $input->getArgument('use-case');
        $entity = $input->getArgument('entity');

        // Clean up names
        $context = str_replace('\\', '', $context);
        $context = ltrim($context, 'App\\');

        $useCasePascal = Str::asCamelCase($useCase);
        $useCasePascal = ucfirst($useCasePascal);

        $entityPascal = Str::asCamelCase($entity);
        $entityPascal = ucfirst($entityPascal);

        $entitySnake = Str::asSnakeCase($entity);

        // Determine the service name based on use case
        $serviceName = $this->getServiceName($useCasePascal, $entityPascal);

        // Domain namespace - new structure Domain/{Entity}/
        $domainNamespace = sprintf('%s\\Domain\\%s\\', $context, $entityPascal);

        // Generate domain service class (e.g., ArticleCreator)
        $classNameDetails = $generator->createClassNameDetails(
            $serviceName,
            $domainNamespace
        );

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/domain/Creator.tpl.php',
            [
                'use_case' => $useCasePascal,
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'context' => $context,
            ]
        );

        // No longer generate interfaces - following YAGNI principle

        // Generate Model in Shared directory
        $sharedNamespace = sprintf('%s\\Domain\\%s\\Shared\\', $context, $entityPascal);
        $modelClassDetails = $generator->createClassNameDetails(
            $entityPascal,
            $sharedNamespace . 'Model\\'
        );

        $generator->generateClass(
            $modelClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/domain/Model.tpl.php',
            [
                'use_case' => $useCasePascal,
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'event_name' => $this->getEventName($useCasePascal),
                'context' => $context,
            ]
        );

        // Generate Event in Shared directory
        $eventClassDetails = $generator->createClassNameDetails(
            $this->getEventName($useCasePascal),
            $sharedNamespace . 'Event\\'
        );

        $generator->generateClass(
            $eventClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/domain/Event.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'context' => $context,
                'event_action' => $this->getEventAction($useCasePascal),
            ]
        );

        // Generate Exception in Shared directory
        $exceptionClassDetails = $generator->createClassNameDetails(
            $entityPascal . 'AlreadyExists',
            $sharedNamespace . 'Exception\\'
        );

        $generator->generateClass(
            $exceptionClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/domain/Exception.tpl.php',
            [
                'entity' => $entityPascal,
                'entity_snake' => $entitySnake,
                'context' => $context,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($consoleStyle);

        $consoleStyle->text([
            'Next steps:',
            sprintf(' - Define the business logic in <info>%s</info>', $classNameDetails->getFullName()),
            sprintf(' - Add value objects and business rules to <info>%s</info>', $modelClassDetails->getFullName()),
            sprintf(' - Customize the domain event in <info>%s</info>', $eventClassDetails->getFullName()),
            ' - Create corresponding Command/Query handlers in Application layer',
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencyBuilder): void
    {
        // No additional dependencies needed
    }

    private function getServiceName(string $useCase, string $entity): string
    {
        // CreateArticle -> ArticleCreator
        // UpdateArticle -> ArticleUpdater
        // PublishArticle -> ArticlePublisher
        $action = preg_replace('/^(Create|Update|Delete|Publish|Submit|Approve|Reject)/', '$1', $useCase);

        return match ($action) {
            'Create' . $entity => $entity . 'Creator',
            'Update' . $entity => $entity . 'Updater',
            'Delete' . $entity => $entity . 'Deleter',
            'Publish' . $entity => $entity . 'Publisher',
            'Submit' . $entity => $entity . 'Submitter',
            'Approve' . $entity => $entity . 'Approver',
            'Reject' . $entity => $entity . 'Rejector',
            default => $entity . 'Service',
        };
    }

    private function getEventName(string $useCase): string
    {
        // CreateArticle -> ArticleCreated
        // UpdateArticle -> ArticleUpdated
        // PublishArticle -> ArticlePublished
        $action = preg_replace('/^(Create|Update|Delete|Publish|Submit|Approve|Reject)/', '$1', $useCase);
        $entity = preg_replace('/^(Create|Update|Delete|Publish|Submit|Approve|Reject)/', '', $useCase);

        return match ($action) {
            'Create' . $entity => $entity . 'Created',
            'Update' . $entity => $entity . 'Updated',
            'Delete' . $entity => $entity . 'Deleted',
            'Publish' . $entity => $entity . 'Published',
            'Submit' . $entity => $entity . 'Submitted',
            'Approve' . $entity => $entity . 'Approved',
            'Reject' . $entity => $entity . 'Rejected',
            default => $useCase . 'Occurred',
        };
    }

    private function getEventAction(string $useCase): string
    {
        // CreateArticle -> Created
        // UpdateArticle -> Updated
        return preg_replace('/^(Create|Update|Delete|Publish|Submit|Approve|Reject).*/', '$1d', $useCase);
    }

    private function getCustomHelpFileContents(string $fileName): string
    {
        return file_get_contents(__DIR__ . '/Resources/help/' . $fileName);
    }
}
