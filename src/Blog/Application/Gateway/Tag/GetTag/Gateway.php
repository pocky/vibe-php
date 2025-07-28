<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\GetTag;

final class Gateway
{
    public function __invoke(Request $request): Response
    {
        // TODO: Implement get tag logic
        return new Response(
            tag: [
                'id' => $request->id,
                'name' => 'TODO',
                'slug' => 'todo',
                'articleCount' => 0,
                'createdAt' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
                'updatedAt' => new \DateTimeImmutable()->format(\DateTimeInterface::ATOM),
            ]
        );
    }
}
