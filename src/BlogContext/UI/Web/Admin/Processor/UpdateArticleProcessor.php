<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Processor;

use App\BlogContext\Application\Gateway\UpdateArticle\Gateway as UpdateArticleGateway;
use App\BlogContext\Application\Gateway\UpdateArticle\Request as UpdateArticleRequest;
use App\BlogContext\UI\Web\Admin\Resource\ArticleResource;
use App\Shared\Application\Gateway\GatewayException;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;

final readonly class UpdateArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private UpdateArticleGateway $updateArticleGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var ArticleResource $data */
        if (!$data instanceof ArticleResource) {
            throw new \InvalidArgumentException('Expected ArticleResource');
        }

        $request = $context->get(RequestOption::class)?->request();
        if (!$request) {
            throw new \RuntimeException('Request not found in context');
        }

        $articleId = $request->attributes->get('id');
        if (!$articleId) {
            throw new \RuntimeException('Article ID not found in request');
        }

        try {
            $gatewayRequest = UpdateArticleRequest::fromData([
                'articleId' => $articleId,
                'title' => $data->title,
                'content' => $data->content,
                'slug' => $data->slug,
                'status' => $data->status,
            ]);

            $gatewayResponse = ($this->updateArticleGateway)($gatewayRequest);
            $responseData = $gatewayResponse->data();

            // Return updated resource with response data
            return new ArticleResource(
                id: $responseData['articleId'],
                title: $responseData['title'],
                content: $responseData['content'],
                slug: $responseData['slug'],
                status: $responseData['status'],
                createdAt: $data->createdAt,
                updatedAt: new \DateTimeImmutable(),
                publishedAt: $data->publishedAt,
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('Article not found', 404, $e);
            }
            if (str_contains($e->getMessage(), 'already exists')) {
                throw new \RuntimeException('Article with this slug already exists', 409, $e);
            }
            throw $e;
        }
    }
}
