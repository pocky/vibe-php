<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BlogContext\Application\Gateway\CreateArticle\Gateway as CreateArticleGateway;
use App\BlogContext\Application\Gateway\CreateArticle\Request as CreateArticleRequest;
use App\BlogContext\Domain\CreateArticle\Exception\ArticleAlreadyExists;
use App\BlogContext\UI\Api\Rest\Resource\ArticleResource;
use App\Shared\Application\Gateway\GatewayException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final readonly class CreateArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private CreateArticleGateway $createArticleGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var ArticleResource $data */
        try {
            $request = CreateArticleRequest::fromData([
                'title' => $data->title,
                'content' => $data->content,
                'slug' => $data->slug,
                'status' => $data->status ?? 'draft',
                'createdAt' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
            ]);

            $response = ($this->createArticleGateway)($request);

            // Update resource with created data
            $responseData = $response->data();

            return new ArticleResource(
                id: $responseData['articleId'],
                title: $data->title,
                content: $data->content,
                slug: $responseData['slug'],
                status: $responseData['status'],
                publishedAt: isset($responseData['publishedAt']) && $responseData['publishedAt']
                    ? new \DateTimeImmutable($responseData['publishedAt'])
                    : null,
            );
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage(), 422, $e);
        } catch (ArticleAlreadyExists $e) {
            throw new ConflictHttpException('Article with this slug already exists', $e);
        } catch (GatewayException $e) {
            // Check if the previous exception is ArticleAlreadyExists
            $previous = $e->getPrevious();
            if ($previous instanceof ArticleAlreadyExists) {
                throw new ConflictHttpException('Article with this slug already exists', $e);
            }
            // Check if the message contains 'already exists'
            if (str_contains($e->getMessage(), 'already exists')) {
                throw new ConflictHttpException('Article with this slug already exists', $e);
            }
            throw $e;
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                throw new ConflictHttpException('Article with this slug already exists', $e);
            }
            throw $e;
        }
    }
}
