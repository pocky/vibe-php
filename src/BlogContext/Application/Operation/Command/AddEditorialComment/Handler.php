<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\AddEditorialComment;

use App\BlogContext\Infrastructure\Persistence\Doctrine\ORM\Entity\BlogEditorialComment;
use App\Shared\Infrastructure\Generator\GeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class Handler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GeneratorInterface $generator,
    ) {
    }

    public function __invoke(Command $command): Result
    {
        $id = $this->generator::generate();
        $createdAt = new \DateTimeImmutable();

        $comment = new BlogEditorialComment(
            id: Uuid::fromString($id),
            articleId: Uuid::fromString($command->articleId),
            reviewerId: Uuid::fromString($command->reviewerId),
            comment: $command->comment,
            createdAt: $createdAt,
            selectedText: $command->selectedText,
            positionStart: $command->positionStart,
            positionEnd: $command->positionEnd,
        );

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return new Result(
            id: $id,
            articleId: $command->articleId,
            reviewerId: $command->reviewerId,
            comment: $command->comment,
            createdAt: $createdAt,
            selectedText: $command->selectedText,
            positionStart: $command->positionStart,
            positionEnd: $command->positionEnd,
        );
    }
}
