<?php

declare(strict_types=1);

namespace App\Domain\User\PasswordReset\Event;

use App\Event\Loggable\LoggableNoticeEventInterface;

class PasswordResetInitiatedForNonExistentUserEvent implements LoggableNoticeEventInterface
{
    private string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    /**
     * @codeCoverageIgnore
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
        return sprintf("Password reset initiated for non existent user with email address %s", $this->email);
    }
}
