<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Article\DeleteArticle;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Article ID is required')]
        #[Assert\Uuid(message: 'Article ID must be a valid UUID')]
        public string $articleId,
        #[Assert\NotBlank(message: 'DeletedBy is required')]
        #[Assert\Uuid(message: 'DeletedBy must be a valid UUID')]
        public string $deletedBy,
    ) {
    }

    public static function fromData(array $data): self
    {
        return new self(
            articleId: $data['articleId'],
            deletedBy: $data['deletedBy'],
        );
    }

    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'deletedBy' => $this->deletedBy,
        ];
    }
}
