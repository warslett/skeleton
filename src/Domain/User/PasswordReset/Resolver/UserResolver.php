<?php

declare(strict_types=1);

namespace App\Domain\User\PasswordReset\Resolver;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\User\PasswordReset\Exception\PasswordResetTokenExpiredException;
use App\Factory\DateTimeFactory;

class UserResolver
{
    private DateTimeFactory $dateTimeFactory;

    public function __construct(DateTimeFactory $dateTimeFactory)
    {
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @param PasswordResetToken $token
     * @return User
     * @throws PasswordResetTokenExpiredException
     */
    public function resolveFromToken(PasswordResetToken $token): User
    {
        $now = $this->dateTimeFactory->createNow();
        if ($token->getExpiry() < $now) {
            throw new PasswordResetTokenExpiredException("Your password reset link has expired, please try again");
        }

        return $token->getUser();
    }
}
