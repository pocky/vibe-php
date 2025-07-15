<?php

declare(strict_types=1);

namespace App\BlogContext\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

final readonly class DeleteArticleProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        // For now, minimal implementation for tests
        // In real implementation, this would call DeleteArticle Gateway
        // when it's implemented in the Application layer

        // API Platform expects null for successful deletion
        return null;
    }
}
