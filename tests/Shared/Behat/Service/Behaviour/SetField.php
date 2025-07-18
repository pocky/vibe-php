<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Service\Behaviour;

use App\Tests\Shared\Behat\Service\Formatter\StringInflector;

trait SetField
{
    use ElementAccessor;

    public function setFieldTo(string $field, string $value): void
    {
        $this->getElement(StringInflector::nameToCode($field))->setValue($value);
    }
}
