<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Context\Page;

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Session;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractShowPage extends AbstractAdminPage
{
    public function __construct(Session $session, \ArrayAccess $minkParameters, RouterInterface $router)
    {
        parent::__construct($session, $minkParameters, $router);
    }

    public function getValidationMessage(string $element): string
    {
        $foundElement = $this->getFieldElement($element);

        $validationMessage = $foundElement->find('css', '.sylius-validation-error');
        if (null === $validationMessage) {
            throw new ElementNotFoundException($this->getSession(), 'Validation message', 'css', '.sylius-validation-error');
        }

        return $validationMessage->getText();
    }
}
