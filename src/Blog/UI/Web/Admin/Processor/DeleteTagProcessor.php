<?php

declare(strict_types=1);

namespace App\Blog\UI\Web\Admin\Processor;

use App\Blog\Application\Gateway\Tag\DeleteTag\Gateway as DeleteTagGateway;
use App\Blog\Application\Gateway\Tag\DeleteTag\Request as DeleteTagRequest;
use App\Blog\UI\Web\Admin\Resource\TagResource;
use App\Shared\Application\Gateway\GatewayException;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;

final readonly class DeleteTagProcessor implements ProcessorInterface
{
    public function __construct(
        private DeleteTagGateway $deleteTagGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var TagResource $data */
        if (!$data instanceof TagResource) {
            throw new \InvalidArgumentException('Expected TagResource');
        }

        if (null === $data->id) {
            throw new \InvalidArgumentException('Tag ID is required for deletion');
        }

        try {
            $gatewayRequest = DeleteTagRequest::fromData([
                'tagId' => $data->id,
            ]);

            ($this->deleteTagGateway)($gatewayRequest);

            // Return null for successful deletion
            return null;
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('Tag not found', 404, $e);
            }

            throw $e;
        }
    }
}
