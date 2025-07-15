<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway;

interface GatewayResponse
{
    public function data(): array;
}
