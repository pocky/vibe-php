<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateAuthor;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $authorId,
        public string $name,
        public string $email,
        public string $bio,
        public bool $success = true,
    ) {
    }

    public function data(): array
    {
        return [
            'authorId' => $this->authorId,
            'name' => $this->name,
            'email' => $this->email,
            'bio' => $this->bio,
            'success' => $this->success,
        ];
    }
}
