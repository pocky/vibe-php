<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Provider;

use App\BlogContext\Application\Gateway\GetArticle\Gateway as GetArticleGateway;
use App\BlogContext\Application\Gateway\GetArticle\Request as GetArticleRequest;
use App\BlogContext\UI\Web\Admin\Resource\EditorialArticleResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class EditorialArticleItemProvider implements ProviderInterface
{
    public function __construct(
        private GetArticleGateway $getArticleGateway,
    ) {
    }

    public function provide(Operation $operation, Context $context): EditorialArticleResource|null
    {
        /** @var RequestOption $requestOption */
        $requestOption = $context->get(RequestOption::class);
        $request = $requestOption->request();

        $id = $request->attributes->get('id');
        if (null === $id) {
            return null;
        }

        try {
            $getRequest = GetArticleRequest::fromData([
                'id' => $id,
            ]);
            $response = ($this->getArticleGateway)($getRequest);
            $data = $response->data();

            if (!isset($data['article'])) {
                return null;
            }

            /** @var array<string, mixed> $article */
            $article = $data['article'];

            return new EditorialArticleResource(
                id: $article['id'],
                title: $article['title'],
                content: $article['content'] ?? '',
                slug: $article['slug'],
                status: $article['status'],
                authorId: $article['author_id'] ?? null,
                authorName: $article['author_name'] ?? 'Unknown',
                submittedAt: isset($article['submitted_at']) ? new \DateTimeImmutable($article['submitted_at']) : null,
                reviewedAt: isset($article['reviewed_at']) ? new \DateTimeImmutable($article['reviewed_at']) : null,
                reviewerId: $article['reviewer_id'] ?? null,
                reviewerName: $article['reviewer_name'] ?? null,
                reviewDecision: $article['review_decision'] ?? null,
                reviewReason: $article['review_reason'] ?? null,
            );
        } catch (\Exception) {
            return null;
        }
    }
}
