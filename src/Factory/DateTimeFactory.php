<?php

declare(strict_types=1);

namespace App\Factory;

use DateTimeImmutable;
use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;

class DateTimeFactory
{
    private LoggerInterface $logger;
    private string $now = 'now';

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $modify
     * @return DateTimeImmutable
     */
    public function createModified(string $modify): DateTimeImmutable
    {
        return $this->createNow()->modify($modify);
    }

    /**
     * @return DateTimeImmutable
     */
    public function createNow(): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($this->now);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * @internal - overrides the current time for testing only
     * @param string $now
     */
    public function spoofNow(string $now): void
    {
        $this->logger->warning(sprintf("Spoofing \"now\" to date \"%s\"", $now));
        $this->now = $now;
    }
}
