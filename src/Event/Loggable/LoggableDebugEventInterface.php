<?php

declare(strict_types=1);

namespace App\Event\Loggable;

interface LoggableDebugEventInterface
{

    /**
     * @return string
     */
    public function getDebugLogMessage(): string;
}
