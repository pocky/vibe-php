<?php

declare(strict_types=1);

namespace App\Blog\UI\Web\Admin\Processor;

use App\Blog\Application\Gateway\Tag\CreateTag\Gateway as CreateTagGateway;
use App\Blog\Application\Gateway\Tag\CreateTag\Request as CreateTagRequest;
use App\Blog\Domain\Tag\Shared\Exception\TagAlreadyExists;
use App\Blog\UI\Web\Admin\Resource\TagResource;
use App\Shared\Application\Gateway\GatewayException;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;

final readonly class CreateTagProcessor implements ProcessorInterface
{
    public function __construct(
        private CreateTagGateway $createTagGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var TagResource $data */
        if (!$data instanceof TagResource) {
            throw new \InvalidArgumentException('Expected TagResource');
        }

        if (null === $data->name || '' === trim($data->name)) {
            throw new \InvalidArgumentException('Tag name is required');
        }

        try {
            $requestData = [
                'name' => $data->name,
            ];

            if (null !== $data->slug && '' !== trim($data->slug)) {
                $requestData['slug'] = $data->slug;
            }

            $gatewayRequest = CreateTagRequest::fromData($requestData);
            $gatewayResponse = ($this->createTagGateway)($gatewayRequest);
            $responseData = $gatewayResponse->data();

            // Return updated resource with generated data
            return new TagResource(
                id: $responseData['id'],
                name: $responseData['name'],
                slug: $responseData['slug'],
                articleCount: 0,
                createdAt: new \DateTimeImmutable($responseData['createdAt']),
                updatedAt: new \DateTimeImmutable($responseData['createdAt']),
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (TagAlreadyExists $e) {
            throw new \RuntimeException('Tag already exists', 409, $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                throw new \RuntimeException('Tag already exists', 409, $e);
            }

            throw $e;
        }
    }
}
