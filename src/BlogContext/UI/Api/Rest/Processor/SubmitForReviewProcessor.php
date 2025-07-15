<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BlogContext\Application\Gateway\SubmitForReview\Gateway as SubmitForReviewGateway;
use App\BlogContext\Application\Gateway\SubmitForReview\Request as SubmitForReviewRequest;
use App\BlogContext\UI\Api\Rest\Resource\ArticleResource;
use App\Shared\Application\Gateway\GatewayException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class SubmitForReviewProcessor implements ProcessorInterface
{
    public function __construct(
        private SubmitForReviewGateway $submitForReviewGateway,
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

            $request = SubmitForReviewRequest::fromData([
                'articleId' => $uriVariables['id'],
                'authorId' => $requestBody['authorId'] ?? '',
            ]);

            $response = ($this->submitForReviewGateway)($request);
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
            if (str_contains($e->getMessage(), 'already submitted') || str_contains($e->getMessage(), 'published')) {
                throw new UnprocessableEntityHttpException($e->getMessage(), $e);
            }
            throw $e;
        }
    }
}
