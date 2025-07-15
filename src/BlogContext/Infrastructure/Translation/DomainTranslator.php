<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Translation;

use App\BlogContext\Domain\Shared\Translation\TranslatorInterface as DomainTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;

final readonly class DomainTranslator implements DomainTranslatorInterface
{
    public function __construct(
        private SymfonyTranslatorInterface $translator,
    ) {}

    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }
}