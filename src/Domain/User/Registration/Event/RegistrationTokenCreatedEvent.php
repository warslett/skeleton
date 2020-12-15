<?php

declare(strict_types=1);

namespace App\Domain\User\Registration\Event;

use App\Domain\Entity\RegistrationToken;
use App\Event\Loggable\LoggableDebugEventInterface;
use App\Event\Loggable\LoggableInfoEventInterface;

class RegistrationTokenCreatedEvent implements LoggableInfoEventInterface, LoggableDebugEventInterface
{
    private RegistrationToken $token;

    public function __construct(RegistrationToken $token)
    {
        $this->token = $token;
    }

    /**
     * @return RegistrationToken
     */
    public function getToken(): RegistrationToken
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getInfoLogMessage(): string
    {
        return sprintf("Registration token created for %s", $this->token->getEmail());
    }

    public function getDebugLogMessage(): string
    {
        /** @var string $tokenPlainText */
        $tokenPlainText = $this->token->getTokenPlainText();
        return sprintf("Registration token for %s is %s", $this->token->getEmail(), $tokenPlainText);
    }
}
