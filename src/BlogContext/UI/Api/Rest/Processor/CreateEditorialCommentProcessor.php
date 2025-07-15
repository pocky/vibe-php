<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BlogContext\Application\Gateway\AddEditorialComment\Gateway as AddEditorialCommentGateway;
use App\BlogContext\Application\Gateway\AddEditorialComment\Request as AddEditorialCommentRequest;
use App\BlogContext\UI\Api\Rest\Resource\EditorialCommentResource;
use App\Shared\Application\Gateway\GatewayException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class CreateEditorialCommentProcessor implements ProcessorInterface
{
    public function __construct(
        private AddEditorialCommentGateway $addEditorialCommentGateway,
    ) {
    }

    #[\Override]
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var EditorialCommentResource $data */
        try {
            $request = AddEditorialCommentRequest::fromData([
                'articleId' => $uriVariables['articleId'] ?? $data->articleId,
                'reviewerId' => $data->reviewerId,
                'comment' => $data->comment,
                'selectedText' => $data->selectedText,
                'positionStart' => $data->positionStart,
                'positionEnd' => $data->positionEnd,
            ]);

            $response = ($this->addEditorialCommentGateway)($request);
            $responseData = $response->data();

            return new EditorialCommentResource(
                id: $responseData['id'],
                articleId: $responseData['articleId'],
                reviewerId: $responseData['reviewerId'],
                comment: $responseData['comment'],
                createdAt: new \DateTimeImmutable($responseData['createdAt']),
                selectedText: $responseData['selectedText'] ?? null,
                positionStart: $responseData['positionStart'] ?? null,
                positionEnd: $responseData['positionEnd'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (GatewayException|\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException('Article not found', $e);
            }
            throw $e;
        }
    }
}
