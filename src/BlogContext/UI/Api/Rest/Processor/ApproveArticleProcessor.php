<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BlogContext\Application\Gateway\ApproveArticle\Gateway as ApproveArticleGateway;
use App\BlogContext\Application\Gateway\ApproveArticle\Request as ApproveArticleRequest;
use App\BlogContext\UI\Api\Rest\Resource\ArticleResource;
use App\Shared\Application\Gateway\GatewayException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class ApproveArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private ApproveArticleGateway $approveArticleGateway,
    ) {
    }

    #[\Override]
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var ArticleResource $data */
        try {
            // Get request body
            $httpRequest = $context['request'] ?? null;
            if (!$httpRequest instanceof Request) {
                throw new UnprocessableEntityHttpException('Invalid request context');
            }

            /** @var array<string, mixed> $requestBody */
            $requestBody = json_decode($httpRequest->getContent(), true) ?? [];

            $request = ApproveArticleRequest::fromData([
                'articleId' => $uriVariables['id'],
                'reviewerId' => $requestBody['reviewerId'] ?? '',
                'reason' => $requestBody['reason'] ?? null,
            ]);

            $response = ($this->approveArticleGateway)($request);
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
            if (str_contains($e->getMessage(), 'not submitted') || str_contains($e->getMessage(), 'invalid status') || str_contains($e->getMessage(), 'Cannot review')) {
                throw new UnprocessableEntityHttpException($e->getMessage(), $e);
            }
            throw $e;
        }
    }
}
