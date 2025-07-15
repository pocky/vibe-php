<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BlogContext\Application\Gateway\RejectArticle\Gateway as RejectArticleGateway;
use App\BlogContext\Application\Gateway\RejectArticle\Request as RejectArticleRequest;
use App\BlogContext\UI\Api\Rest\Resource\ArticleResource;
use App\Shared\Application\Gateway\GatewayException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class RejectArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private RejectArticleGateway $rejectArticleGateway,
    ) {
    }

    #[\Override]
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var ArticleResource $data */
        try {
            // Get request body
            $httpRequest = $context['request'] ?? null;
            if (!$httpRequest instanceof \Symfony\Component\HttpFoundation\Request) {
                throw new UnprocessableEntityHttpException('Invalid request context');
            }

            /** @var array<string, mixed> $requestBody */
            $requestBody = json_decode($httpRequest->getContent(), true) ?? [];

            if (empty($requestBody['reason'])) {
                throw new \InvalidArgumentException('Rejection reason is required');
            }

            $request = RejectArticleRequest::fromData([
                'articleId' => $uriVariables['id'],
                'reviewerId' => $requestBody['reviewerId'] ?? '',
                'reason' => $requestBody['reason'],
            ]);

            $response = ($this->rejectArticleGateway)($request);
            $responseData = $response->data();

            // Return updated article resource
            return new ArticleResource(
                id: $responseData['articleId'],
                title: $data->title,
                content: $data->content,
                slug: $data->slug,
                status: $responseData['status'],
                publishedAt: $data->publishedAt,
            );
        } catch (\InvalidArgumentException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException('Article not found', $e);
            }
            if (str_contains($e->getMessage(), 'not submitted') || str_contains($e->getMessage(), 'invalid status')) {
                throw new UnprocessableEntityHttpException($e->getMessage(), $e);
            }
            throw $e;
        }
    }
}
