<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogEditorialComment;
use App\BlogContext\UI\Api\Rest\Resource\EditorialCommentResource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class ListEditorialCommentsProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[\Override]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['articleId'])) {
            return [];
        }

        $articleId = $uriVariables['articleId'];
        if (!Uuid::isValid($articleId)) {
            return [];
        }

        $repository = $this->entityManager->getRepository(BlogEditorialComment::class);
        $qb = $repository->createQueryBuilder('c')
            ->where('c.articleId = :articleId')
            ->setParameter('articleId', Uuid::fromString($articleId))
            ->orderBy('c.createdAt', 'DESC');

        $comments = $qb->getQuery()->getResult();

        return array_map(
            fn (BlogEditorialComment $comment) => new EditorialCommentResource(
                id: $comment->getId()->toRfc4122(),
                articleId: $comment->getArticleId()->toRfc4122(),
                reviewerId: $comment->getReviewerId()->toRfc4122(),
                comment: $comment->getComment(),
                createdAt: $comment->getCreatedAt(),
                selectedText: $comment->getSelectedText(),
                positionStart: $comment->getPositionStart(),
                positionEnd: $comment->getPositionEnd(),
            ),
            $comments
        );
    }
}
