<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Translation;

use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class TranslationService
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function translateDataCorruption(string $entityClass, string $entityId, string $errorKey): string
    {
        return $this->translator->trans('error.data_corruption.entity', [
            'entity_class' => $entityClass,
            'entity_id' => $entityId,
            'error_key' => $errorKey,
        ], 'messages');
    }

    public function translateRepositoryError(string $operation, string $entityClass, string $errorKey): string
    {
        return $this->translator->trans('error.repository.operation_failed', [
            'operation' => $operation,
            'entity_class' => $entityClass,
            'error_key' => $errorKey,
        ], 'messages');
    }

    public function translateValidationError(string $validationKey, array $parameters = []): string
    {
        return $this->translator->trans($validationKey, $parameters, 'messages');
    }
}
