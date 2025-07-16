<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Behat\Page;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Session;
use Symfony\Component\Routing\RouterInterface;

abstract class SymfonyPage implements PageInterface
{
    public function __construct(
        protected readonly Session $session,
        protected readonly RouterInterface $router,
        protected array $parameters = []
    ) {
    }

    public function open(array $urlParameters = []): void
    {
        $this->session->visit($this->getUrl($urlParameters));
    }

    public function isOpen(): bool
    {
        return $this->session->getPage()->has('css', $this->getDefinedElements()['page'] ?? 'body');
    }

    public function getUrl(array $urlParameters = []): string
    {
        return $this->router->generate($this->getRouteName(), $urlParameters);
    }

    abstract protected function getRouteName(): string;

    /**
     * @return array<string, string>
     */
    protected function getDefinedElements(): array
    {
        return [];
    }

    protected function getElement(string $name): NodeElement
    {
        $element = $this->session->getPage()->find('css', $this->getDefinedElements()[$name] ?? throw new \InvalidArgumentException(sprintf('Element "%s" is not defined', $name)));

        if (null === $element) {
            throw new ElementNotFoundException($this->session->getDriver(), 'element', 'css', $this->getDefinedElements()[$name]);
        }

        return $element;
    }

    protected function hasElement(string $name): bool
    {
        return $this->session->getPage()->has('css', $this->getDefinedElements()[$name] ?? throw new \InvalidArgumentException(sprintf('Element "%s" is not defined', $name)));
    }

    protected function waitForElement(string $name, int $timeout = 5): NodeElement
    {
        $element = null;
        $end = time() + $timeout;

        while (time() < $end) {
            $element = $this->session->getPage()->find('css', $this->getDefinedElements()[$name]);
            if (null !== $element) {
                break;
            }
            usleep(100000); // 100ms
        }

        if (null === $element) {
            throw new ElementNotFoundException($this->session->getDriver(), 'element', 'css', $this->getDefinedElements()[$name]);
        }

        return $element;
    }

    public function getSession(): Session
    {
        return $this->session;
    }
}
