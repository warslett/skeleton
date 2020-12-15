<?php

declare(strict_types=1);

namespace App\Event\Loggable;

interface LoggableInfoEventInterface
{

    /**
     * @return string
     */
    public function getInfoLogMessage(): string;
}
