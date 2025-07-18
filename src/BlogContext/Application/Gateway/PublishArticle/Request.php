<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\PublishArticle;

use App\BlogContext\Application\Gateway\PublishArticle\Constraint\SeoReady;
use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Validator\Constraints as Assert;

#[SeoReady]
final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Article ID is required')]
        #[Assert\Uuid(message: 'Article ID must be a valid UUID')]
        public string $articleId,
    ) {
    }

    public static function fromData(array $data): self
    {
        return new self(
            articleId: $data['articleId'] ?? throw new \InvalidArgumentException('Article ID is required'),
        );
    }

    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
        ];
    }
}
