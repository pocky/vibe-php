<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BlogContext\Application\Gateway\GetArticle\Gateway as GetArticleGateway;
use App\BlogContext\Application\Gateway\GetArticle\Request as GetArticleRequest;
use App\BlogContext\UI\Api\Rest\Resource\ArticleResource;
use App\Shared\Application\Gateway\GatewayException;

final readonly class GetArticleProvider implements ProviderInterface
{
    public function __construct(
        private GetArticleGateway $getArticleGateway,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['id'])) {
            return null;
        }

        try {
            $request = GetArticleRequest::fromData([
                'id' => $uriVariables['id'],
            ]);
            $response = ($this->getArticleGateway)($request);

            $data = $response->data();

            return [] === $data ? null : $this->transformToResource($data);
        } catch (GatewayException $e) {
            // Check if the previous exception is our "not found" runtime exception
            $previous = $e->getPrevious();
            if ($previous instanceof \RuntimeException && 'Article not found' === $previous->getMessage()) {
                return null; // API Platform will return 404
            }
            throw $e;
        } catch (\RuntimeException $e) {
            if ('Article not found' === $e->getMessage()) {
                return null; // API Platform will return 404
            }
            throw $e;
        }
    }

    private function transformToResource(array $data): ArticleResource
    {
        return new ArticleResource(
            id: $data['id'],
            title: $data['title'],
            content: $data['content'],
            slug: $data['slug'],
            status: $data['status'],
            publishedAt: isset($data['published_at']) && $data['published_at']
                ? new \DateTimeImmutable($data['published_at'])
                : null,
        );
    }
}
