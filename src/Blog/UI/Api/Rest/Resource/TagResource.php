<?php

declare(strict_types=1);

namespace App\Blog\UI\Api\Rest\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\Blog\UI\Api\Rest\Processor\CreateTagProcessor;
use App\Blog\UI\Api\Rest\Processor\DeleteTagProcessor;
use App\Blog\UI\Api\Rest\Processor\UpdateTagProcessor;
use App\Blog\UI\Api\Rest\Provider\TagCollectionProvider;
use App\Blog\UI\Api\Rest\Provider\TagItemProvider;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Tag',
    description: 'Blog tag resource',
    operations: [
        new Get(
            uriTemplate: '/blog/tags/{id}',
            openapi: new Operation(
                tags: ['Blog'],
                summary: 'Get a tag',
                description: 'Retrieves a tag by its identifier',
            ),
            normalizationContext: [
                'groups' => ['tag:read', 'tag:item:read'],
            ],
            provider: TagItemProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/blog/tags',
            openapi: new Operation(
                tags: ['Blog'],
                summary: 'List tags',
                description: 'Retrieves the collection of tags',
                parameters: [
                    [
                        'name' => 'page',
                        'in' => 'query',
                        'description' => 'The collection page number',
                        'required' => false,
                        'schema' => [
                            'type' => 'integer',
                            'default' => 1,
                        ],
                    ],
                    [
                        'name' => 'itemsPerPage',
                        'in' => 'query',
                        'description' => 'The number of items per page',
                        'required' => false,
                        'schema' => [
                            'type' => 'integer',
                            'default' => 20,
                            'minimum' => 1,
                            'maximum' => 100,
                        ],
                    ],
                ],
            ),
            paginationEnabled: true,
            paginationItemsPerPage: 20,
            paginationMaximumItemsPerPage: 100,
            normalizationContext: [
                'groups' => ['tag:read', 'tag:collection:read'],
            ],
            provider: TagCollectionProvider::class,
        ),
        new Post(
            uriTemplate: '/blog/tags',
            openapi: new Operation(
                tags: ['Blog'],
                responses: [
                    '201' => [
                        'description' => 'Tag created',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Tag.jsonld-tag.read_tag.item.read',
                                ],
                            ],
                        ],
                    ],
                    '400' => [
                        'description' => 'Invalid input',
                    ],
                    '422' => [
                        'description' => 'Unprocessable entity',
                    ],
                ],
                summary: 'Create a tag',
                description: 'Creates a new tag',
                requestBody: new RequestBody(
                    description: 'The tag to create',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => [
                                        'type' => 'string',
                                        'description' => 'The tag name',
                                        'example' => 'Technology',
                                    ],
                                    'slug' => [
                                        'type' => 'string',
                                        'description' => 'The tag slug (optional, auto-generated if not provided)',
                                        'example' => 'technology',
                                    ],
                                ],
                                'required' => ['name'],
                            ],
                        ],
                    ]),
                    required: true,
                ),
            ),
            normalizationContext: [
                'groups' => ['tag:read', 'tag:item:read'],
            ],
            denormalizationContext: [
                'groups' => ['tag:create'],
            ],
            validationContext: [
                'groups' => ['Default', 'tag:create'],
            ],
            processor: CreateTagProcessor::class,
        ),
        new Put(
            uriTemplate: '/blog/tags/{id}',
            openapi: new Operation(
                tags: ['Blog'],
                responses: [
                    '200' => [
                        'description' => 'Tag updated',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Tag.jsonld-tag.read_tag.item.read',
                                ],
                            ],
                        ],
                    ],
                    '400' => [
                        'description' => 'Invalid input',
                    ],
                    '404' => [
                        'description' => 'Tag not found',
                    ],
                    '422' => [
                        'description' => 'Unprocessable entity',
                    ],
                ],
                summary: 'Update a tag',
                description: 'Updates an existing tag',
                requestBody: new RequestBody(
                    description: 'The tag data to update',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => [
                                        'type' => 'string',
                                        'description' => 'The tag name',
                                        'example' => 'Technology',
                                    ],
                                    'slug' => [
                                        'type' => 'string',
                                        'description' => 'The tag slug',
                                        'example' => 'technology',
                                    ],
                                ],
                                'required' => ['name', 'slug'],
                            ],
                        ],
                    ]),
                    required: true,
                ),
            ),
            normalizationContext: [
                'groups' => ['tag:read', 'tag:item:read'],
            ],
            denormalizationContext: [
                'groups' => ['tag:update'],
            ],
            validationContext: [
                'groups' => ['Default', 'tag:update'],
            ],
            processor: UpdateTagProcessor::class,
        ),
        new Delete(
            uriTemplate: '/blog/tags/{id}',
            openapi: new Operation(
                tags: ['Blog'],
                responses: [
                    '204' => [
                        'description' => 'Tag deleted',
                    ],
                    '404' => [
                        'description' => 'Tag not found',
                    ],
                ],
                summary: 'Delete a tag',
                description: 'Deletes a tag',
            ),
            processor: DeleteTagProcessor::class,
        ),
    ],
    normalizationContext: [
        'groups' => ['tag:read'],
    ],
    denormalizationContext: [
        'groups' => ['tag:write'],
    ],
)]
final class TagResource
{
    #[Groups(['tag:read'])]
    public \DateTimeImmutable|null $createdAt = null;

    #[Groups(['tag:read'])]
    public \DateTimeImmutable|null $updatedAt = null;

    public function __construct(
        #[Groups(['tag:read'])]
        public string|null $id = null,
        #[Assert\NotBlank(groups: ['tag:create', 'tag:update'])]
        #[Assert\Length(min: 1, max: 100, groups: ['tag:create', 'tag:update'])]
        #[Groups(['tag:read', 'tag:create', 'tag:update'])]
        public string|null $name = null,
        #[Assert\Length(max: 100, groups: ['tag:create', 'tag:update'])]
        #[Assert\Regex(
            pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            message: 'The slug can only contain lowercase letters, numbers, and hyphens',
            groups: ['tag:create', 'tag:update']
        )]
        #[Groups(['tag:read', 'tag:create', 'tag:update'])]
        public string|null $slug = null,
        \DateTimeInterface|string|null $createdAt = null,
        \DateTimeInterface|string|null $updatedAt = null,
    ) {
        // Handle DateTime conversion from strings
        if (is_string($createdAt)) {
            try {
                $this->createdAt = new \DateTimeImmutable($createdAt);
            } catch (\Exception) {
                $this->createdAt = null;
            }
        } else {
            $this->createdAt = $createdAt instanceof \DateTimeImmutable
                ? $createdAt
                : ($createdAt instanceof \DateTime ? \DateTimeImmutable::createFromMutable($createdAt) : null);
        }

        if (is_string($updatedAt)) {
            try {
                $this->updatedAt = new \DateTimeImmutable($updatedAt);
            } catch (\Exception) {
                $this->updatedAt = null;
            }
        } else {
            $this->updatedAt = $updatedAt instanceof \DateTimeImmutable
                ? $updatedAt
                : ($updatedAt instanceof \DateTime ? \DateTimeImmutable::createFromMutable($updatedAt) : null);
        }
    }
}
