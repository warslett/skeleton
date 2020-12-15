<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

final class CleanSlateContext implements Context
{
    private ORMPurger $purger;

    public function __construct(ORMPurger $purger)
    {
        $this->purger = $purger;
    }

    /**
     * @BeforeScenario
     * @return void
     */
    public function clearData(): void
    {
        $this->purger->purge();
    }
}
