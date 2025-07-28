<?php

declare(strict_types=1);

namespace App\Blog\UI\Web\Admin\Processor;

use App\Blog\Application\Gateway\Tag\UpdateTag\Gateway as UpdateTagGateway;
use App\Blog\Application\Gateway\Tag\UpdateTag\Request as UpdateTagRequest;
use App\Blog\UI\Web\Admin\Resource\TagResource;
use App\Shared\Application\Gateway\GatewayException;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;

final readonly class UpdateTagProcessor implements ProcessorInterface
{
    public function __construct(
        private UpdateTagGateway $updateTagGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var TagResource $data */
        if (!$data instanceof TagResource) {
            throw new \InvalidArgumentException('Expected TagResource');
        }

        if (null === $data->id) {
            throw new \InvalidArgumentException('Tag ID is required for update');
        }

        if (null === $data->name || '' === trim($data->name)) {
            throw new \InvalidArgumentException('Tag name is required');
        }

        try {
            $requestData = [
                'tagId' => $data->id,
                'name' => $data->name,
            ];

            if (null !== $data->slug && '' !== trim($data->slug)) {
                $requestData['slug'] = $data->slug;
            }

            $gatewayRequest = UpdateTagRequest::fromData($requestData);
            $gatewayResponse = ($this->updateTagGateway)($gatewayRequest);
            $responseData = $gatewayResponse->data();

            // Return updated resource
            return new TagResource(
                id: $responseData['id'],
                name: $responseData['name'],
                slug: $responseData['slug'],
                articleCount: $data->articleCount,
                createdAt: $data->createdAt,
                updatedAt: new \DateTimeImmutable($responseData['updatedAt']),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('Tag not found', 404, $e);
            }

            throw $e;
        }
    }
}
