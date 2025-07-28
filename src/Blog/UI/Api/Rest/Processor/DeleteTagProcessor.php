<?php

declare(strict_types=1);

namespace App\Blog\UI\Api\Rest\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Blog\Application\Gateway\Tag\DeleteTag\Gateway as DeleteTagGateway;
use App\Blog\Application\Gateway\Tag\DeleteTag\Request as DeleteTagRequest;
use App\Blog\UI\Api\Rest\Resource\TagResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<TagResource, void>
 */
final readonly class DeleteTagProcessor implements ProcessorInterface
{
    public function __construct(
        private DeleteTagGateway $deleteTagGateway,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $id = $uriVariables['id'] ?? null;
        if (!$id) {
            throw new NotFoundHttpException('Tag not found');
        }

        $request = new DeleteTagRequest(id: $id);

        try {
            ($this->deleteTagGateway)($request);
        } catch (\Exception $exception) {
            if (str_contains($exception->getMessage(), 'not found')) {
                throw new NotFoundHttpException('Tag not found', $exception);
            }

            throw $exception;
        }
    }
}
