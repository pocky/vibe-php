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
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class PublishArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private PublishArticleGateway $publishArticleGateway,
        private TranslatorInterface $translator,
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
            $message = $this->translator->trans('error.article.not_found', [], 'messages');
            throw new NotFoundHttpException($message, $e);
        } catch (ArticleAlreadyPublished $e) {
            $message = $this->translator->trans('error.article.already_published', [], 'messages');
            throw new ConflictHttpException($message, $e);
        } catch (ArticleNotReady $e) {
            // The error message from DefaultValidation already contains the validation errors
            // We need to translate them for the API response
            $errorMessage = $e->getMessage();

            // Extract validation errors from the message
            if (str_contains($errorMessage, 'validation.')) {
                // Parse the validation errors and translate them
                $lines = explode("\n", $errorMessage);
                $translatedLines = [];

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (str_starts_with($line, 'validation.')) {
                        $translatedLines[] = $this->translator->trans($line, [], 'messages');
                    } elseif ('' !== $line && !str_contains($line, 'Object(')) {
                        $translatedLines[] = $line;
                    }
                }

                $translatedMessage = implode("\n", $translatedLines);
            } else {
                $translatedMessage = $errorMessage;
            }

            throw new UnprocessableEntityHttpException($translatedMessage, $e);
        } catch (GatewayException $e) {
            $previous = $e->getPrevious();
            if ($previous instanceof ArticleNotFound) {
                $message = $this->translator->trans('error.article.not_found', [], 'messages');
                throw new NotFoundHttpException($message, $e);
            }
            if ($previous instanceof ArticleAlreadyPublished) {
                $message = $this->translator->trans('error.article.already_published', [], 'messages');
                throw new ConflictHttpException($message, $e);
            }
            if ($previous instanceof ArticleNotReady) {
                // Handle validation errors same as above
                $errorMessage = $previous->getMessage();

                if (str_contains($errorMessage, 'validation.')) {
                    $lines = explode("\n", $errorMessage);
                    $translatedLines = [];

                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (str_starts_with($line, 'validation.')) {
                            $translatedLines[] = $this->translator->trans($line, [], 'messages');
                        } elseif ('' !== $line && !str_contains($line, 'Object(')) {
                            $translatedLines[] = $line;
                        }
                    }

                    $translatedMessage = implode("\n", $translatedLines);
                } else {
                    $translatedMessage = $errorMessage;
                }

                throw new UnprocessableEntityHttpException($translatedMessage, $e);
            }

            // Check if the GatewayException contains validation errors
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'validation.') && str_contains($errorMessage, 'DefaultValidation.php')) {
                $lines = explode("\n", $errorMessage);
                $translatedLines = [];

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (str_starts_with($line, 'validation.')) {
                        $translatedLines[] = $this->translator->trans($line, [], 'messages');
                    }
                }

                if ([] !== $translatedLines) {
                    $translatedMessage = implode("\n", $translatedLines);
                    throw new UnprocessableEntityHttpException($translatedMessage, $e);
                }
            }

            throw $e;
        }
    }
}
