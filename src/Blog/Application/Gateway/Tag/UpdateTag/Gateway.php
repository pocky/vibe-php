<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\UpdateTag;

final class Gateway
{
    public function __invoke(Request $request): Response
    {
        // TODO: Implement update tag logic
        return new Response(
            id: $request->id,
            name: $request->name,
            slug: $request->slug ?? 'todo',
            updatedAt: new \DateTimeImmutable()
        );
    }
}
