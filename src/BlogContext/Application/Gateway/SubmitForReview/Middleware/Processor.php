<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\SubmitForReview\Middleware;

use App\BlogContext\Application\Gateway\SubmitForReview\Request;
use App\BlogContext\Application\Gateway\SubmitForReview\Response;
use App\BlogContext\Application\Operation\Command\SubmitForReview\Command;
use App\BlogContext\Application\Operation\Command\SubmitForReview\Handler;
use App\BlogContext\Domain\Shared\Model\Article;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\Shared\Application\Gateway\GatewayException;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Processor
{
    public function __construct(
        private Handler $handler,
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        if (!$request instanceof Request) {
            throw new GatewayException('Invalid request type', new \InvalidArgumentException('Invalid request type'));
        }

        // Verify article exists and get current status
        $articleId = new ArticleId($request->articleId);
        $article = $this->articleRepository->findById($articleId);

        if (!$article instanceof Article) {
            throw new GatewayException('Article not found', new \RuntimeException('Article not found'));
        }

        $command = new Command(
            articleId: $request->articleId,
            authorId: $request->authorId,
        );

        try {
            ($this->handler)($command);
        } catch (\RuntimeException $e) {
            throw new GatewayException($e->getMessage(), $e);
        }

        return new Response(
            articleId: $request->articleId,
            status: ArticleStatus::PENDING_REVIEW->getValue(),
            submittedAt: new \DateTimeImmutable(),
        );
    }
}
