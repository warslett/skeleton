<?php

declare(strict_types=1);

namespace App\Domain\User\PasswordReset\Event;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Event\Loggable\LoggableInfoEventInterface;
use Symfony\Component\Uid\Ulid;

class PasswordResetEvent implements LoggableInfoEventInterface
{
    private User $user;
    private PasswordResetToken $token;

    public function __construct(User $user, PasswordResetToken $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * @codeCoverageIgnore
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
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
        $ulid = $this->user->getUlid();
        return sprintf("Password reset for %s", $ulid);
    }
}
