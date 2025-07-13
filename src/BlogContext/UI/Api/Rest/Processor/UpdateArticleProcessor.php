<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BlogContext\Application\Gateway\UpdateArticle\Gateway as UpdateArticleGateway;
use App\BlogContext\Application\Gateway\UpdateArticle\Request as UpdateArticleRequest;
use App\BlogContext\UI\Api\Rest\Resource\ArticleResource;

final readonly class UpdateArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private UpdateArticleGateway $updateArticleGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var ArticleResource $data */
        try {
            $request = UpdateArticleRequest::fromData([
                'articleId' => $uriVariables['id'],
                'title' => $data->title,
                'content' => $data->content,
                'slug' => $data->slug,
                'status' => $data->status,
            ]);

            $response = ($this->updateArticleGateway)($request);

            // Update resource with response data
            $responseData = $response->data();

            return new ArticleResource(
                id: $responseData['articleId'],
                title: $responseData['title'],
                content: $responseData['content'],
                slug: $responseData['slug'],
                status: $responseData['status'],
                publishedAt: $data->publishedAt,
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('Article not found', 404, $e);
            }
            throw $e;
        }
    }
}
