<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogEditorialComment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

final readonly class DeleteEditorialCommentProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[\Override]
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
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

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        // Return null for DELETE operations (results in 204 No Content)
        return null;
    }
}
