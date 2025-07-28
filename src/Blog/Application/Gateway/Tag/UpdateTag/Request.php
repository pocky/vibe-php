<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\UpdateTag;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Tag ID cannot be blank')]
        #[Assert\Uuid(message: 'Tag ID must be a valid UUID')]
        public string $id,
        #[Assert\NotBlank(message: 'Tag name cannot be blank')]
        #[Assert\Length(
            min: 2,
            max: 100,
            minMessage: 'Tag name must be at least {{ limit }} characters long',
            maxMessage: 'Tag name cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Type('string')]
        public string $name,
        #[Assert\Length(
            max: 150,
            maxMessage: 'Tag slug cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            message: 'Tag slug must contain only lowercase letters, numbers, and hyphens'
        )]
        public string|null $slug = null,
    ) {
    }

    public static function fromData(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            slug: $data['slug'] ?? null,
        );
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
