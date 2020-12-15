<?php

declare(strict_types=1);

namespace App\Event\Loggable;

interface LoggableNoticeEventInterface
{

    /**
     * @return string
     */
    public function getNoticeLogMessage(): string;
}
