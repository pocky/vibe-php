<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\DeleteTag;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Tag ID is required')]
        #[Assert\Uuid(message: 'Tag ID must be a valid UUID')]
        public string $id,
    ) {
    }

    public static function fromData(array $data): self
    {
        return new self(
            id: $data['id'],
        );
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
