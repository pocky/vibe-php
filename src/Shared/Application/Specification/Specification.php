<?php

declare(strict_types=1);

namespace App\Shared\Application\Specification;

interface Specification
{
    public function isSatisfiedBy(object $candidate): bool;
}
