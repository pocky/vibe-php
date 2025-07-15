<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Processor;

use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\UI\Web\Admin\Resource\ArticleResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;

final readonly class DeleteArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
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
            // Create ArticleId from string
            $domainArticleId = new ArticleId($articleId);

            // Create a simple domain object for deletion
            $articleToDelete = new readonly class($domainArticleId) {
                public function __construct(
                    public ArticleId $id,
                ) {
                }
            };

            // Use the repository to delete the article
            $this->articleRepository->remove($articleToDelete);

            // Return null to indicate successful deletion
            return null;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new \RuntimeException('Article not found', 404, $e);
            }
            throw $e;
        }
    }
}
