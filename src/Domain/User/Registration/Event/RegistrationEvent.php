<?php

declare(strict_types=1);

namespace App\Domain\User\Registration\Event;

use App\Domain\Entity\RegistrationToken;
use App\Domain\Entity\User;
use App\Event\Loggable\LoggableInfoEventInterface;
use Symfony\Component\Uid\Ulid;

class RegistrationEvent implements LoggableInfoEventInterface
{
    private User $user;
    private RegistrationToken $token;

    public function __construct(User $user, RegistrationToken $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
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
        /** @var Ulid $ulid */
        $ulid = $this->user->getUlid();
        return sprintf("Registered %s", $ulid);
    }
}
