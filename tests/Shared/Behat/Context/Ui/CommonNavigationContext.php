<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Context\Ui;

use Behat\Behat\Context\Context;
use Behat\Mink\Session;

/**
 * @final
 */
class CommonNavigationContext implements Context
{
    public function __construct(
        private readonly Session $session,
    ) {
    }

    #[\Behat\Step\Given('I am on the admin dashboard')]
    public function iAmOnTheAdminDashboard(): void
    {
        $this->session->visit('/admin');
    }

    #[\Behat\Step\When('I go to :path')]
    public function iGoTo(string $path): void
    {
        $this->session->visit($path);
    }

    #[\Behat\Step\Then('I should see :text in the title')]
    public function iShouldSeeInTheTitle(string $text): void
    {
        $pageText = $this->session->getPage()->getText();
        if (!str_contains($pageText, $text)) {
            throw new \RuntimeException(sprintf('Text "%s" was not found on the page', $text));
        }
    }
}
