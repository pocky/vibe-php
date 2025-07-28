<?php

declare(strict_types=1);

namespace App\Blog\UI\Web\Admin\Provider;

use App\Blog\Application\Gateway\Tag\GetTag\Gateway as GetTagGateway;
use App\Blog\Application\Gateway\Tag\GetTag\Request as GetTagRequest;
use App\Blog\UI\Web\Admin\Resource\TagResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class TagItemProvider implements ProviderInterface
{
    public function __construct(
        private GetTagGateway $getTagGateway,
    ) {
    }

    public function provide(Operation $operation, Context $context): object|array|null
    {
        /** @var RequestOption|null $requestOption */
        $requestOption = $context->get(RequestOption::class);
        $request = $requestOption?->request();
        $id = $request?->attributes->get('id');

        if (null === $id) {
            return null;
        }

        try {
            $gatewayRequest = GetTagRequest::fromData([
                'id' => $id,
            ]);
            $gatewayResponse = ($this->getTagGateway)($gatewayRequest);

            $data = $gatewayResponse->data();

            return isset($data['tag']) ? $this->transformToResource($data['tag']) : null;
        } catch (\RuntimeException $runtimeException) {
            if (str_contains($runtimeException->getMessage(), 'not found')) {
                return null;
            }

            throw $runtimeException;
        }
    }

    private function transformToResource(array $data): TagResource
    {
        return new TagResource(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            articleCount: $data['articleCount'] ?? 0,
            createdAt: isset($data['createdAt']) && $data['createdAt']
                ? new \DateTimeImmutable($data['createdAt'])
                : null,
            updatedAt: isset($data['updatedAt']) && $data['updatedAt']
                ? new \DateTimeImmutable($data['updatedAt'])
                : null,
        );
    }
}
