<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $namespace; ?>\Middleware\Processor;
use App\Shared\Application\Gateway\Attribute\AsGateway;
use App\Shared\Application\Gateway\DefaultGateway;
use App\Shared\Application\Gateway\Middleware\DefaultErrorHandler;
use App\Shared\Application\Gateway\Middleware\DefaultLogger;
use App\Shared\Application\Gateway\Middleware\DefaultValidation;

#[AsGateway(
    context: '<?php echo $context; ?>',
    domain: '<?php echo $entity; ?>',
    operation: '<?php echo $operation; ?>',
    middlewares: [],
)]
final class <?php echo $class_name; ?> extends DefaultGateway
{
    public function __construct(
        Middleware\Processor $processor,
    ) {
        parent::__construct([
            $processor,
        ]);
    }
}
