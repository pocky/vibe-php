<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateAuthor;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $authorId,
        public string $name,
        public string $email,
        public string $bio = '',
    ) {
        if ('' === trim($this->authorId)) {
            throw new \InvalidArgumentException('Author ID is required');
        }

        if ('' === trim($this->name)) {
            throw new \InvalidArgumentException('Name is required');
        }

        if ('' === trim($this->email)) {
            throw new \InvalidArgumentException('Email is required');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            authorId: $data['authorId'] ?? '',
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
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
