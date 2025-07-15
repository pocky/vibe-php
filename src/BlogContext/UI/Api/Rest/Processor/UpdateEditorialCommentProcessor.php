<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogEditorialComment;
use App\BlogContext\UI\Api\Rest\Resource\EditorialCommentResource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

final readonly class UpdateEditorialCommentProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[\Override]
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var EditorialCommentResource $data */
        if (!isset($uriVariables['id'])) {
            throw new NotFoundHttpException('Comment not found');
        }

        $id = $uriVariables['id'];
        if (!Uuid::isValid($id)) {
            throw new NotFoundHttpException('Invalid comment ID');
        }

        $comment = $this->entityManager->find(BlogEditorialComment::class, Uuid::fromString($id));
        if (!$comment instanceof BlogEditorialComment) {
            throw new NotFoundHttpException('Comment not found');
        }

        // Update only the comment text (as per the test scenario)
        if (null !== $data->comment) {
            $comment->setComment($data->comment);
        }

        $this->entityManager->flush();

        return new EditorialCommentResource(
            id: $comment->getId()->toRfc4122(),
            articleId: $comment->getArticleId()->toRfc4122(),
            reviewerId: $comment->getReviewerId()->toRfc4122(),
            comment: $comment->getComment(),
            createdAt: $comment->getCreatedAt(),
            selectedText: $comment->getSelectedText(),
            positionStart: $comment->getPositionStart(),
            positionEnd: $comment->getPositionEnd(),
        );
    }
}
