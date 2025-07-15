<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway\Middleware;

use App\BlogContext\Domain\Shared\Exception\ValidationException;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class TranslationErrorHandler
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        try {
            /** @var GatewayResponse */
            return $next($request);
        } catch (ValidationException $e) {
            // Translate the validation exception
            $translatedMessage = $this->translator->trans(
                $e->getTranslationKey(),
                $e->getTranslationParameters(),
                $e->getTranslationDomain()
            );

            // Throw a new exception with the translated message
            throw new \InvalidArgumentException($translatedMessage, $e->getCode(), $e);
        }
    }
}
