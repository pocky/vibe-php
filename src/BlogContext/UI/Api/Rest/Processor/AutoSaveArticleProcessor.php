<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BlogContext\Application\Gateway\AutoSaveArticle\Gateway as AutoSaveArticleGateway;
use App\BlogContext\Application\Gateway\AutoSaveArticle\Request as AutoSaveArticleRequest;
use App\BlogContext\UI\Api\Rest\Resource\ArticleResource;
use App\Shared\Application\Gateway\GatewayException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class AutoSaveArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private AutoSaveArticleGateway $autoSaveArticleGateway,
    ) {
    }

    #[\Override]
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var ArticleResource $data */
        if (!isset($uriVariables['id'])) {
            throw new \RuntimeException('Article ID is required');
        }

        try {
            $request = AutoSaveArticleRequest::fromData([
                'articleId' => $uriVariables['id'],
                'title' => $data->title,
                'content' => $data->content,
                'slug' => $data->slug,
            ]);

            $response = ($this->autoSaveArticleGateway)($request);
            $responseData = $response->data();

            // Return the updated resource
            return new ArticleResource(
                id: $responseData['articleId'],
                title: $responseData['title'],
                content: $responseData['content'],
                slug: $responseData['slug'],
                status: $responseData['status'],
                publishedAt: $data->publishedAt, // Preserve existing published date
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage(), $e);
            }
            if (str_contains($e->getMessage(), 'Cannot auto-save published article')
                || str_contains($e->getMessage(), 'Published article')
                || str_contains($e->getMessage(), 'requires editor approval')) {
                throw new BadRequestHttpException('Cannot auto-save published article', $e);
            }
            throw $e;
        }
    }
}
