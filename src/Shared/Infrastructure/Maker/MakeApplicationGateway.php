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

final class MakeApplicationGateway extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:application:gateway';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new Application Gateway with Request/Response pattern';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('context', InputArgument::REQUIRED, 'The context name (e.g., BlogContext)')
            ->addArgument('operation', InputArgument::REQUIRED, 'The operation name (e.g., CreateArticle)')
            ->setHelp($this->getCustomHelpFileContents('MakeApplicationGateway.txt'))
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $context = $input->getArgument('context');
        $operation = $input->getArgument('operation');

        // Clean up context name (remove Context suffix if present, App prefix)
        $context = str_replace('\\', '', $context);
        $context = ltrim($context, 'App\\');
        if (str_ends_with($context, 'Context')) {
            $context = substr($context, 0, -7);
        }
        $context .= 'Context';

        // Prepare operation names
        $operationPascalCase = Str::asCamelCase($operation);
        $operationPascalCase = ucfirst($operationPascalCase);
        $operationSnakeCase = Str::asSnakeCase($operation);

        // Extract operation type and entity from operation name
        preg_match('/^(Create|Update|Delete|Get|List|Publish|Submit|Approve|Reject|Add)(.+)$/', $operationPascalCase, $matches);
        $operationType = $matches[1] ?? 'Execute';
        $entity = $matches[2] ?? $operationPascalCase;

        // Determine if it's a command or query
        $isCommand = in_array($operationType, ['Create', 'Update', 'Delete', 'Publish', 'Submit', 'Approve', 'Reject', 'Add']);
        $isQuery = in_array($operationType, ['Get', 'List']);

        // Determine domain from operation (e.g., CreateArticle -> article)
        $domain = preg_replace('/^(Create|Update|Delete|Get|List|Publish|Submit|Approve|Reject|Add)/', '', $operationPascalCase);
        $domainSnakeCase = Str::asSnakeCase($domain);

        // Prepare entity variations
        $entityCamel = lcfirst($entity);
        $entitySnake = Str::asSnakeCase($entity);

        // Gateway namespace
        $gatewayNamespace = sprintf('%s\\Application\\Gateway\\%s\\', $context, $operationPascalCase);

        // Generate Gateway class
        $gatewayClassDetails = $generator->createClassNameDetails(
            'Gateway',
            $gatewayNamespace
        );

        $generator->generateClass(
            $gatewayClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/gateway/Gateway.tpl.php',
            [
                'context_snake' => Str::asSnakeCase(str_replace('Context', '', $context)),
                'domain_snake' => $domainSnakeCase,
                'operation_snake' => $operationSnakeCase,
            ]
        );

        // Generate Request class
        $requestClassDetails = $generator->createClassNameDetails(
            'Request',
            $gatewayNamespace
        );

        $generator->generateClass(
            $requestClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/gateway/Request.tpl.php',
            []
        );

        // Generate Response class
        $responseClassDetails = $generator->createClassNameDetails(
            'Response',
            $gatewayNamespace
        );

        $generator->generateClass(
            $responseClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/gateway/Response.tpl.php',
            []
        );

        // Generate Processor middleware
        $processorClassDetails = $generator->createClassNameDetails(
            'Processor',
            $gatewayNamespace . 'Middleware\\'
        );

        $generator->generateClass(
            $processorClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/gateway/Processor.tpl.php',
            [
                'operation_pascal' => $operationPascalCase,
                'operation_type' => $operationType,
                'operation_snake' => $operationSnakeCase,
                'entity' => $entity,
                'entity_camel' => $entityCamel,
                'entity_snake' => $entitySnake,
                'context' => $context,
                'is_command' => $isCommand,
                'is_query' => $isQuery,
                'domain' => $domain,
                'domain_snake' => $domainSnakeCase,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text([
            'Next steps:',
            sprintf(' - Implement the request validation in <info>%s</info>', $requestClassDetails->getFullName()),
            sprintf(' - Define the response structure in <info>%s</info>', $responseClassDetails->getFullName()),
            sprintf(' - Implement the business logic in <info>%s</info>', $processorClassDetails->getFullName()),
            ' - Create the corresponding Command/Query and Handler if needed',
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
