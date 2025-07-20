<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\<?php echo $context; ?>\Application\Gateway\Update<?php echo $entity; ?>\Gateway as Update<?php echo $entity; ?>Gateway;
use App\<?php echo $context; ?>\Application\Gateway\Update<?php echo $entity; ?>\Request as Update<?php echo $entity; ?>Request;
use App\<?php echo $context; ?>\UI\Api\Rest\Resource\<?php echo $entity; ?>Resource;

final readonly class <?php echo $class_name; ?> implements ProcessorInterface
{
    public function __construct(
        private Update<?php echo $entity; ?>Gateway $update<?php echo $entity; ?>Gateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var <?php echo $entity; ?>Resource $data */
        try {
            $request = Update<?php echo $entity; ?>Request::fromData([
                '<?php echo $entity_camel; ?>Id' => $uriVariables['id'],
                // TODO: Map resource data to gateway request
                // 'title' => $data->title,
                // 'content' => $data->content,
                // 'status' => $data->status,
            ]);

            $response = ($this->update<?php echo $entity; ?>Gateway)($request);
            $responseData = $response->data();

            return new <?php echo $entity; ?>Resource(
                id: $responseData['<?php echo $entity_camel; ?>Id'] ?? $uriVariables['id'],
                // TODO: Map response data back to resource
                // title: $responseData['title'] ?? $data->title,
                // content: $responseData['content'] ?? $data->content,
                createdAt: $data->createdAt,
                updatedAt: new \DateTimeImmutable(),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('<?php echo $entity; ?> not found', 404, $e);
            }
            throw $e;
        }
    }
}
