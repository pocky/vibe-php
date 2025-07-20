<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\<?php echo $context; ?>\Application\Gateway\Create<?php echo $entity; ?>\Gateway as Create<?php echo $entity; ?>Gateway;
use App\<?php echo $context; ?>\Application\Gateway\Create<?php echo $entity; ?>\Request as Create<?php echo $entity; ?>Request;
use App\<?php echo $context; ?>\Domain\Create<?php echo $entity; ?>\Exception\<?php echo $entity; ?>AlreadyExists;
use App\<?php echo $context; ?>\UI\Api\Rest\Resource\<?php echo $entity; ?>Resource;
use App\Shared\Application\Gateway\GatewayException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final readonly class <?php echo $class_name; ?> implements ProcessorInterface
{
    public function __construct(
        private Create<?php echo $entity; ?>Gateway $create<?php echo $entity; ?>Gateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var <?php echo $entity; ?>Resource $data */
        try {
            $request = Create<?php echo $entity; ?>Request::fromData([
                // TODO: Map resource data to gateway request
                // 'title' => $data->title,
                // 'content' => $data->content,
                // 'status' => $data->status ?? 'draft',
                'createdAt' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
            ]);

            $response = ($this->create<?php echo $entity; ?>Gateway)($request);
            $responseData = $response->data();

            return new <?php echo $entity; ?>Resource(
                id: $responseData['<?php echo $entity_camel; ?>Id'],
                // TODO: Map response data back to resource
                // title: $data->title,
                // content: $data->content,
                createdAt: new \DateTimeImmutable(),
                updatedAt: new \DateTimeImmutable(),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (<?php echo $entity; ?>AlreadyExists|GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                throw new ConflictHttpException('<?php echo $entity; ?> already exists', $e);
            }
            throw $e;
        }
    }
}
