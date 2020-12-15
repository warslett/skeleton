<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Psr\Log\LoggerInterface;

final class LoggingContext implements Context
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @BeforeScenario
     * @param BeforeScenarioScope $scope
     * @return void
     */
    public function logStartScenario(BeforeScenarioScope $scope): void
    {
        $this->logger->debug(sprintf("Begin Scenario: %s", $scope->getScenario()->getTitle() ?? 'Unknown Scenario'));
    }

    /**
     * @BeforeStep
     * @param BeforeStepScope $scope
     * @return void
     */
    public function logStartStep(BeforeStepScope $scope): void
    {
        $this->logger->debug(sprintf("Begin Step: %s", $scope->getStep()->getText()));
    }
}
