<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Provider;

use App\BlogContext\Application\Gateway\ListArticles\Gateway as ListArticlesGateway;
use App\BlogContext\Application\Gateway\ListArticles\Request as ListArticlesRequest;
use App\BlogContext\UI\Web\Admin\Resource\EditorialArticleResource;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

final readonly class EditorialArticleGridProvider implements DataProviderInterface
{
    public function __construct(
        private ListArticlesGateway $listArticlesGateway,
    ) {
    }

    public function getData(Grid $grid, Parameters $parameters): Pagerfanta
    {
        $page = (int) $parameters->get('page', 1);
        $limit = (int) $parameters->get('limit', 20);
        $criteria = $parameters->get('criteria', []);
        $status = is_array($criteria) ? ($criteria['status'] ?? 'pending_review') : 'pending_review';
        $sorting = $parameters->get('sorting', []);

        $request = ListArticlesRequest::fromData([
            'page' => $page,
            'limit' => $limit,
            'status' => $status,
        ]);

        $response = ($this->listArticlesGateway)($request);
        $responseData = $response->data();

        // Transform response to EditorialArticleResource objects
        /** @var array<EditorialArticleResource> $articles */
        $articles = [];
        if (isset($responseData['articles']) && is_array($responseData['articles'])) {
            foreach ($responseData['articles'] as $articleData) {
                if (is_array($articleData)) {
                    $articles[] = $this->transformToResource($articleData);
                }
            }
        }

        $adapter = new ArrayAdapter($articles);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setCurrentPage($page);
        $pagerfanta->setMaxPerPage($limit);

        return $pagerfanta;
    }

    private function transformToResource(array $data): EditorialArticleResource
    {
        return new EditorialArticleResource(
            id: $data['id'],
            title: $data['title'],
            content: $data['content'] ?? '',
            slug: $data['slug'],
            status: $data['status'],
            authorId: $data['author_id'] ?? null,
            authorName: $data['author_name'] ?? 'Unknown',
            submittedAt: isset($data['submitted_at']) ? new \DateTimeImmutable($data['submitted_at']) : null,
            reviewedAt: isset($data['reviewed_at']) ? new \DateTimeImmutable($data['reviewed_at']) : null,
            reviewerId: $data['reviewer_id'] ?? null,
            reviewerName: $data['reviewer_name'] ?? null,
            reviewDecision: $data['review_decision'] ?? null,
            reviewReason: $data['review_reason'] ?? null,
        );
    }
}
