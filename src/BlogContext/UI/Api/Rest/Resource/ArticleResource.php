<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\BlogContext\UI\Api\Rest\Processor\CreateArticleProcessor;
use App\BlogContext\UI\Api\Rest\Processor\DeleteArticleProcessor;
use App\BlogContext\UI\Api\Rest\Processor\PublishArticleProcessor;
use App\BlogContext\UI\Api\Rest\Processor\UpdateArticleProcessor;
use App\BlogContext\UI\Api\Rest\Provider\GetArticleProvider;
use App\BlogContext\UI\Api\Rest\Provider\ListArticlesProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Article',
    operations: [
        new Get(
            uriTemplate: '/articles/{id}',
            name: 'api_articles_get',
            provider: GetArticleProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/articles',
            name: 'api_articles_get_collection',
            provider: ListArticlesProvider::class,
        ),
        new Post(
            uriTemplate: '/articles',
            name: 'api_articles_post',
            processor: CreateArticleProcessor::class,
        ),
        new Put(
            uriTemplate: '/articles/{id}',
            name: 'api_articles_put',
            provider: GetArticleProvider::class,
            processor: UpdateArticleProcessor::class,
        ),
        new Delete(
            uriTemplate: '/articles/{id}',
            name: 'api_articles_delete',
            processor: DeleteArticleProcessor::class,
        ),
        new Post(
            uriTemplate: '/articles/{id}/publish',
            description: 'Publish an existing article',
            read: true,
            deserialize: false,
            name: 'api_articles_publish',
            provider: GetArticleProvider::class,
            processor: PublishArticleProcessor::class,
        ),
    ],
)]
final class ArticleResource
{
    public function __construct(
        public string|null $id = null,
        #[Assert\NotBlank(message: 'Title is required')]
        #[Assert\Length(min: 3, max: 200, minMessage: 'Title must be at least 3 characters', maxMessage: 'Title cannot exceed 200 characters')]
        public string|null $title = null,
        #[Assert\NotBlank(message: 'Content is required')]
        #[Assert\Length(min: 10, minMessage: 'Content must be at least 10 characters')]
        public string|null $content = null,
        #[Assert\NotBlank(message: 'Slug is required')]
        #[Assert\Regex(pattern: '/^[a-z0-9-]+$/', message: 'Slug must contain only lowercase letters, numbers and hyphens')]
        public string|null $slug = null,
        public string|null $status = null,
        public \DateTimeImmutable|null $publishedAt = null,
    ) {
    }
}
