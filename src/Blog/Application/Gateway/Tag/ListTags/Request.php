<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\ListTags;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\Positive(message: 'Page must be a positive number')]
        public int $page = 1,
        #[Assert\Range(
            min: 1,
            max: 100,
            notInRangeMessage: 'Items per page must be between {{ min }} and {{ max }}'
        )]
        public int $itemsPerPage = 20,
        #[Assert\Length(
            max: 100,
            maxMessage: 'Name filter cannot be longer than {{ limit }} characters'
        )]
        public string|null $nameFilter = null,
    ) {
    }

    public static function fromData(array $data): self
    {
        return new self(
            page: (int) ($data['page'] ?? 1),
            itemsPerPage: (int) ($data['itemsPerPage'] ?? 20),
            nameFilter: $data['name'] ?? null,
        );
    }

    public function data(): array
    {
        return [
            'page' => $this->page,
            'itemsPerPage' => $this->itemsPerPage,
            'nameFilter' => $this->nameFilter,
        ];
    }
}
