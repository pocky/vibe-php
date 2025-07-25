<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateAuthor;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $name,
        public string $email,
        public string $bio = '',
    ) {
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
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            bio: $data['bio'] ?? '',
        );
    }

    public function data(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'bio' => $this->bio,
        ];
    }
}
