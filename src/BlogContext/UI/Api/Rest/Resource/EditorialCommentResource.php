<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\BlogContext\UI\Api\Rest\Processor\CreateEditorialCommentProcessor;
use App\BlogContext\UI\Api\Rest\Processor\DeleteEditorialCommentProcessor;
use App\BlogContext\UI\Api\Rest\Processor\UpdateEditorialCommentProcessor;
use App\BlogContext\UI\Api\Rest\Provider\GetEditorialCommentProvider;
use App\BlogContext\UI\Api\Rest\Provider\ListEditorialCommentsProvider;

#[ApiResource(
    shortName: 'Comment',
    operations: [
        new Get(
            uriTemplate: '/comments/{id}',
            provider: GetEditorialCommentProvider::class,
        ),
        new Put(
            uriTemplate: '/comments/{id}',
            provider: GetEditorialCommentProvider::class,
            processor: UpdateEditorialCommentProcessor::class,
        ),
        new Delete(
            uriTemplate: '/comments/{id}',
            provider: GetEditorialCommentProvider::class,
            processor: DeleteEditorialCommentProcessor::class,
        ),
    ],
)]
#[ApiResource(
    shortName: 'ArticleComment',
    uriTemplate: '/articles/{articleId}/comments',
    operations: [
        new GetCollection(
            provider: ListEditorialCommentsProvider::class,
        ),
        new Post(
            processor: CreateEditorialCommentProcessor::class,
        ),
    ],
    uriVariables: [
        'articleId' => new Link(fromClass: ArticleResource::class),
    ],
)]
final class EditorialCommentResource
{
    public function __construct(
        public string|null $id = null,
        public string|null $articleId = null,
        public string|null $reviewerId = null,
        public string|null $comment = null,
        public \DateTimeImmutable|null $createdAt = null,
        public string|null $selectedText = null,
        public int|null $positionStart = null,
        public int|null $positionEnd = null,
    ) {
    }
}
