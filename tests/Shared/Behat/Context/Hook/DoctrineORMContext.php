<?php

declare(strict_types=1);

namespace App\Tests\Shared\Behat\Context\Hook;

use Behat\Behat\Context\Context;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineORMContext implements Context
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[\Behat\Hook\BeforeScenario]
    public function purgeDatabase(): void
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
        $this->entityManager->clear();
    }
}
