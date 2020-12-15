<?php

declare(strict_types=1);

namespace App\Listener\Loggable;

use App\Event\Loggable\LoggableInfoEventInterface;
use Psr\Log\LoggerInterface;

class LogInfoListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(LoggableInfoEventInterface $event)
    {
        $this->logger->info($event->getInfoLogMessage());
    }
}
