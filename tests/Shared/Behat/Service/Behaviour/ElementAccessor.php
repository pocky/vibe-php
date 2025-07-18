<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Service\Behaviour;

use Behat\Mink\Element\NodeElement;

trait ElementAccessor
{
    abstract protected function getElement(string $elementName): NodeElement;

    abstract public function getDefinedElements(): array;
}
