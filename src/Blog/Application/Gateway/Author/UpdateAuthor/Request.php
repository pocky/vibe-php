<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Author\UpdateAuthor;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Author ID is required')]
        #[Assert\Uuid(message: 'Author ID must be a valid UUID')]
        public string $authorId,
        #[Assert\NotBlank(message: 'Name is required')]
        #[Assert\Length(
            min: 2,
            max: 100,
            minMessage: 'Name must be at least {{ limit }} characters long',
            maxMessage: 'Name cannot be longer than {{ limit }} characters'
        )]
        public string $name,
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Email must be a valid email address')]
        public string $email,
        #[Assert\Length(
            max: 500,
            maxMessage: 'Bio cannot be longer than {{ limit }} characters'
        )]
        public string $bio = '',
    ) {
    }

    public static function fromData(array $data): self
    {
        return new self(
            authorId: $data['authorId'],
            name: $data['name'],
            email: $data['email'],
            bio: $data['bio'] ?? '',
        );
    }

    public function data(): array
    {
        return [
            'authorId' => $this->authorId,
            'name' => $this->name,
            'email' => $this->email,
            'bio' => $this->bio,
        ];
    }
}
