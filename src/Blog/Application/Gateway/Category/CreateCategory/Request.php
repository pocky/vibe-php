<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Category\CreateCategory;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Category name cannot be blank')]
        #[Assert\Length(
            min: 2,
            max: 100,
            minMessage: 'Category name must be at least {{ limit }} characters long',
            maxMessage: 'Category name cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Type('string')]
        public string $name,
        #[Assert\NotBlank(message: 'Category description cannot be blank')]
        #[Assert\Length(
            min: 10,
            max: 500,
            minMessage: 'Category description must be at least {{ limit }} characters long',
            maxMessage: 'Category description cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Type('string')]
        public string $description,
        #[Assert\Length(
            max: 150,
            maxMessage: 'Category slug cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            message: 'Category slug must contain only lowercase letters, numbers, and hyphens'
        )]
        public string|null $slug = null,
        #[Assert\Uuid(message: 'Parent ID must be a valid UUID')]
        public string|null $parentId = null,
        #[Assert\PositiveOrZero(message: 'Order must be zero or positive')]
        #[Assert\Type('integer')]
        public int|null $order = null,
    ) {
    }

    public static function fromData(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'],
            slug: $data['slug'] ?? null,
            parentId: $data['parentId'] ?? null,
            order: $data['order'] ?? null,
        );
    }

    public function data(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parentId' => $this->parentId,
            'order' => $this->order,
        ];
    }
}
