<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Behat\Page;

interface PageInterface
{
    /**
     * Opens page
     */
    public function open(array $urlParameters = []): void;

    /**
     * Verifies if the page is open
     */
    public function isOpen(): bool;

    /**
     * Returns page URL
     */
    public function getUrl(array $urlParameters = []): string;
}
