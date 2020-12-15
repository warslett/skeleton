<?php

declare(strict_types=1);

namespace App\Listener\Loggable;

use App\Event\Loggable\LoggableDebugEventInterface;
use Psr\Log\LoggerInterface;

class LogDebugListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(LoggableDebugEventInterface $event)
    {
        $this->logger->debug($event->getDebugLogMessage());
    }
}
