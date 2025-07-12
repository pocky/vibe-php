<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class DefaultValidation
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @template TRequest of GatewayRequest
     * @template TResponse of GatewayResponse
     *
     * @param TRequest $request
     * @param callable(TRequest): TResponse $next
     *
     * @return TResponse
     */
    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        // Use Symfony Validator to validate the request object
        $violations = $this->validator->validate($request);

        if (0 < count($violations)) {
            throw new ValidationFailedException($request, $violations);
        }

        return $next($request);
    }
}
