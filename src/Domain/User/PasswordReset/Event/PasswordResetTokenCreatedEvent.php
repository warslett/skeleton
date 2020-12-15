<?php

declare(strict_types=1);

namespace App\Domain\User\PasswordReset\Event;

use App\Domain\Entity\PasswordResetToken;
use App\Event\Loggable\LoggableDebugEventInterface;
use App\Event\Loggable\LoggableInfoEventInterface;
use Symfony\Component\Uid\Ulid;

class PasswordResetTokenCreatedEvent implements LoggableInfoEventInterface, LoggableDebugEventInterface
{
    private PasswordResetToken $token;

    public function __construct(PasswordResetToken $token)
    {
        $this->token = $token;
    }

    /**
     * @codeCoverageIgnore
     * @return PasswordResetToken
     */
    public function getToken(): PasswordResetToken
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getInfoLogMessage(): string
    {
        /** @var Ulid $ulid */
        $ulid = $this->token->getUser()->getUlid();
        return sprintf("Password reset token created for %s", $ulid);
    }

    public function getDebugLogMessage(): string
    {
        /** @var Ulid $ulid */
        $ulid = $this->token->getUser()->getUlid();
        /** @var string $tokenPlainText */
        $tokenPlainText = $this->token->getTokenPlainText();
        return sprintf("Password reset token for %s is %s", $ulid, $tokenPlainText);
    }
}
