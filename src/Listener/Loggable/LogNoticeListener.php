<?php

declare(strict_types=1);

namespace App\Listener\Loggable;

use App\Event\Loggable\LoggableNoticeEventInterface;
use Psr\Log\LoggerInterface;

class LogNoticeListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(LoggableNoticeEventInterface $event)
    {
        $this->logger->notice($event->getNoticeLogMessage());
    }
}
