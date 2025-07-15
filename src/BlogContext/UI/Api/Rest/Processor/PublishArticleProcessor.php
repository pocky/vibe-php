<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BlogContext\Application\Gateway\PublishArticle\Gateway as PublishArticleGateway;
use App\BlogContext\Application\Gateway\PublishArticle\Request as PublishArticleRequest;
use App\BlogContext\Domain\PublishArticle\Exception\ArticleAlreadyPublished;
use App\BlogContext\Domain\PublishArticle\Exception\ArticleNotFound;
use App\BlogContext\Domain\PublishArticle\Exception\ArticleNotReady;
use App\BlogContext\UI\Api\Rest\Resource\ArticleResource;
use App\Shared\Application\Gateway\GatewayException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class PublishArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private PublishArticleGateway $publishArticleGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!isset($uriVariables['id'])) {
            throw new \InvalidArgumentException('Article ID is required');
        }

        try {
            $request = PublishArticleRequest::fromData([
                'articleId' => $uriVariables['id'],
            ]);

            $response = ($this->publishArticleGateway)($request);
            $responseData = $response->data();

            /** @var ArticleResource $data */
            // The ArticleResource was already loaded by the provider
            // Update it with the new status and published date
            $data->status = (string) $responseData['status'];
            $data->publishedAt = new \DateTimeImmutable($responseData['publishedAt']);

            return $data;
        } catch (ArticleNotFound $e) {
            throw new NotFoundHttpException('Article not found', $e);
        } catch (ArticleAlreadyPublished $e) {
            throw new ConflictHttpException('Article is already published', $e);
        } catch (ArticleNotReady $e) {
            // Create a structured validation error for API Platform
            $violation = new \stdClass();
            $violation->message = $e->getMessage();
            $violation->propertyPath = 'article';

            $violationList = new \stdClass();
            $violationList->{'@type'} = 'ConstraintViolationList';
            $violationList->violations = [$violation];

            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (GatewayException $e) {
            $previous = $e->getPrevious();
            if ($previous instanceof ArticleNotFound) {
                throw new NotFoundHttpException('Article not found', $e);
            }
            if ($previous instanceof ArticleAlreadyPublished) {
                throw new ConflictHttpException('Article is already published', $e);
            }
            if ($previous instanceof ArticleNotReady) {
                // Create a structured validation error for API Platform
                throw new UnprocessableEntityHttpException($previous->getMessage(), $e);
            }
            throw $e;
        }
    }
}
