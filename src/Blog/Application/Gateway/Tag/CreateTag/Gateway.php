<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\CreateTag;

use App\Blog\Application\Shared\Exception\ValidationException;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;
use App\Blog\Domain\Tag\TagCreator;
use App\Blog\Infrastructure\Identity\TagIdGenerator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class Gateway
{
    public function __construct(
        private TagCreator $creator,
        private TagIdGenerator $tagIdGenerator,
        private ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $constraintViolationList = $this->validator->validate($request);

        if (0 < count($constraintViolationList)) {
            throw new ValidationException($constraintViolationList);
        }

        $tagId = $this->tagIdGenerator->nextIdentity();

        $tag = ($this->creator)(
            tagId: $tagId,
            tagName: new TagName($request->name),
            slug: null !== $request->slug && '' !== $request->slug && '0' !== $request->slug ? new TagSlug($request->slug) : null,
        );

        return new Response(
            id: $tag->getId()->getValue(),
            name: $tag->getName()->getValue(),
            slug: $tag->getSlug()->getValue(),
            createdAt: $tag->getCreatedAt(),
        );
    }
}
