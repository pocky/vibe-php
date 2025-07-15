<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BlogContext\Application\Gateway\CreateArticle\Gateway as CreateArticleGateway;
use App\BlogContext\Application\Gateway\CreateArticle\Request as CreateArticleRequest;
use App\BlogContext\Domain\CreateArticle\Exception\ArticleAlreadyExists;
use App\BlogContext\Domain\Shared\Exception\ValidationException;
use App\BlogContext\UI\Api\Rest\Resource\ArticleResource;
use App\Shared\Application\Gateway\GatewayException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class CreateArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private CreateArticleGateway $createArticleGateway,
        private TranslatorInterface $translator,
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
        } catch (ValidationException $e) {
            // Translate validation errors for API consumers
            $message = $this->translator->trans(
                $e->getTranslationKey(),
                $e->getTranslationParameters(),
                $e->getTranslationDomain()
            );
            throw new BadRequestHttpException($message, $e);
        } catch (ArticleAlreadyExists $e) {
            $message = $this->translator->trans('error.article.already_exists', [], 'messages');
            throw new ConflictHttpException($message, $e);
        } catch (GatewayException $e) {
            // Check nested exceptions for ArticleAlreadyExists
            $current = $e;
            while ($current instanceof \Throwable) {
                if ($current instanceof ArticleAlreadyExists) {
                    $message = $this->translator->trans('error.article.already_exists', [], 'messages');
                    throw new ConflictHttpException($message, $e);
                }
                if ($current instanceof ValidationException) {
                    $message = $this->translator->trans(
                        $current->getTranslationKey(),
                        $current->getTranslationParameters(),
                        $current->getTranslationDomain()
                    );
                    throw new BadRequestHttpException($message, $e);
                }

                // Check if message contains "Article with slug" to handle deeply nested exceptions
                if ($current->getMessage() && str_contains($current->getMessage(), 'already exists')) {
                    $message = $this->translator->trans('error.article.already_exists', [], 'messages');
                    throw new ConflictHttpException($message, $e);
                }

                $current = $current->getPrevious();
            }
            throw $e;
        }
    }
}
