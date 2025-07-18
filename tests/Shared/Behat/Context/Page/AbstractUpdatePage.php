<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Context\Page;

use App\Tests\Shared\Behat\Service\Behaviour\SetField;
use App\Tests\Shared\Behat\Service\Formatter\StringInflector;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Session;
use FriendsOfBehat\PageObjectExtension\Page\UnexpectedPageException;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractUpdatePage extends AbstractAdminPage
{
    use SetField;

    public function __construct(Session $session, \ArrayAccess $minkParameters, RouterInterface $router)
    {
        parent::__construct($session, $minkParameters, $router);
    }

    #[\Override]
    protected function verifyStatusCode(): void
    {
        try {
            $statusCode = $this->getSession()->getStatusCode();
        } catch (DriverException) {
            return; // Ignore drivers which cannot check the response status code
        }

        if ((200 <= $statusCode && 299 >= $statusCode) || 422 === $statusCode) {
            return;
        }

        $currentUrl = $this->getSession()->getCurrentUrl();
        $message = sprintf('Could not open the page: "%s". Received an error status code: %s', $currentUrl, $statusCode);

        throw new UnexpectedPageException($message);
    }

    public function saveChanges(): void
    {
        $this->getDocument()->find('css', '[data-test-update-changes-button]')->click();
    }

    public function getValidationMessage($element): string
    {
        $foundElement = $this->getFieldElement($element);

        $validationMessage = $foundElement->find('css', '.sylius-validation-error');
        if (null === $validationMessage) {
            throw new ElementNotFoundException($this->getSession(), 'Validation message', 'css', '.sylius-validation-error');
        }

        return $validationMessage->getText();
    }

    public function hasResourceValues(array $parameters): bool
    {
        foreach ($parameters as $element => $value) {
            if ($this->getElement($element)->getValue() !== (string) $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws ElementNotFoundException
     */
    private function getFieldElement(string $element): NodeElement
    {
        $element = $this->getElement(StringInflector::nameToCode($element));
        while (!$element->hasClass('field')) {
            $element = $element->getParent();
        }

        return $element;
    }
}
