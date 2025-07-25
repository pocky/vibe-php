<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use App\<?php echo $context; ?>\Application\Gateway\<?php echo $operation_pascal; ?>\Request;
use App\<?php echo $context; ?>\Application\Gateway\<?php echo $operation_pascal; ?>\Response;
<?php if ($is_command) { ?>
use App\<?php echo $context; ?>\Application\Operation\Command\<?php echo $operation_pascal; ?>\Command;
use App\<?php echo $context; ?>\Application\Operation\Command\<?php echo $operation_pascal; ?>\Handler;
<?php } elseif ($is_query) { ?>
use App\<?php echo $context; ?>\Application\Operation\Query\<?php echo $operation_pascal; ?>\Query;
use App\<?php echo $context; ?>\Application\Operation\Query\<?php echo $operation_pascal; ?>\Handler;
<?php } ?>
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class <?php echo $class_name; ?>
{
    public function __construct(
<?php if ($is_command || $is_query) { ?>
        private Handler $handler,
<?php } else { ?>
        // Inject your dependencies here
<?php } ?>
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var Request $request */
<?php if ('Create' === $operation_type) { ?>

        // Create command
        $command = new Command(
            // TODO: Map fields from request
            // title: $request->title,
            // content: $request->content,
            // status: $request->status ?? 'draft',
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return response with generated data
        return new Response(
            <?php echo $entity_camel; ?>Id: $command-><?php echo $entity_camel; ?>Id ?? '', // Handler should set ID
            // TODO: Add other response fields
            success: true,
        );
<?php } elseif ('Update' === $operation_type) { ?>

        // Create command
        $command = new Command(
            <?php echo $entity_camel; ?>Id: $request-><?php echo $entity_camel; ?>Id,
            // TODO: Map other fields from request
            // name: $request->name,
            // description: $request->description,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return success response
        return new Response(
            <?php echo $entity_camel; ?>Id: $request-><?php echo $entity_camel; ?>Id,
            // TODO: Add other response fields
        );
<?php } elseif ('Delete' === $operation_type) { ?>

        // Create command
        $command = new Command(
            <?php echo $entity_camel; ?>Id: $request-><?php echo $entity_camel; ?>Id,
        );

        // Execute command through handler
        ($this->handler)($command);

        // Return success response
        return new Response(
            deleted: true,
        );
<?php } elseif ('Get' === $operation_type) { ?>

        // Create query
        $query = new Query(
            <?php echo $entity_camel; ?>Id: $request-><?php echo $entity_camel; ?>Id,
        );

        // Execute query through handler
        $result = ($this->handler)($query);

        // Return response with view data
        return new Response(
            id: $result->id,
            // TODO: Map other fields from view
            createdAt: $result->createdAt,
            updatedAt: $result->updatedAt,
        );
<?php } elseif ('List' === $operation_type) { ?>

        // Create query
        $query = new Query(
            page: $request->page ?? 1,
            limit: $request->limit ?? 20,
            // TODO: Add filters if needed
        );

        // Execute query through handler
        $result = ($this->handler)($query);

        // Return response with collection data
        return new Response(
            <?php echo $entity_camel; ?>s: array_map(
                fn ($item) => $item->toArray(),
                $result->items
            ),
            total: $result->total,
            page: $result->page,
            limit: $result->limit,
        );
<?php } else { ?>

        // TODO: Implement your business logic here
        // Create command or query based on your operation type

        // Return response
        return new Response(
            // Map your response data
        );
<?php } ?>
    }
}

