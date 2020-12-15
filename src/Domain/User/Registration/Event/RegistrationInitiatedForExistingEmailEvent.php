<?php

declare(strict_types=1);

namespace App\Domain\User\Registration\Event;

use App\Event\Loggable\LoggableNoticeEventInterface;

class RegistrationInitiatedForExistingEmailEvent implements LoggableNoticeEventInterface
{
    private string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getNoticeLogMessage(): string
    {
        return sprintf("Registration initiated for existing email %s", $this->email);
    }
}
