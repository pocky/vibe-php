<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Web\Admin\Processor;

use App\BlogContext\Application\Gateway\RejectArticle\Gateway as RejectArticleGateway;
use App\BlogContext\Application\Gateway\RejectArticle\Request as RejectArticleRequest;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;

final readonly class RejectArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private RejectArticleGateway $rejectArticleGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        /** @var RequestOption $requestOption */
        $requestOption = $context->get(RequestOption::class);
        $request = $requestOption->request();

        $id = $request->attributes->get('id');
        if (null === $id) {
            throw new \RuntimeException('Article ID is required');
        }

        $reason = $request->request->get('reason', '');
        if (empty($reason)) {
            throw new \RuntimeException('Rejection reason is required');
        }

        $rejectRequest = RejectArticleRequest::fromData([
            'articleId' => $id,
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440001', // TODO: Get from security context
            'reason' => $reason,
        ]);

        ($this->rejectArticleGateway)($rejectRequest);

        return null;
    }
}
