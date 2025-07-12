<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway;

interface GatewayResponse
{
    /**
     * @return array<string, mixed>
     */
    public function data(): array;
}
