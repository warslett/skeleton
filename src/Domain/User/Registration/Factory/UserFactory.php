<?php

declare(strict_types=1);

namespace App\Domain\User\Registration\Factory;

use App\Domain\Entity\RegistrationToken;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepository;
use App\Domain\User\Registration\Exception\DuplicateEmailException;
use App\Domain\User\Registration\Exception\RegistrationTokenExpiredException;
use App\Factory\DateTimeFactory;

class UserFactory
{
    private DateTimeFactory $dateTimeFactory;
    private UserRepository $userRepository;

    public function __construct(
        DateTimeFactory $dateTimeFactory,
        UserRepository $userRepository
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->userRepository = $userRepository;
    }

    /**
     * @param RegistrationToken $registrationToken
     * @return User
     * @throws DuplicateEmailException
     * @throws RegistrationTokenExpiredException
     */
    public function createFromToken(RegistrationToken $registrationToken): User
    {
        $now = $this->dateTimeFactory->createNow();
        if ($registrationToken->getExpiry() < $now) {
            throw new RegistrationTokenExpiredException("Your registration has expired, please try again");
        }

        $email = $registrationToken->getEmail();
        if ($this->userRepository->countByEmail($email) > 0) {
            throw new DuplicateEmailException("Your email is already registered");
        }

        $user = new User();
        $user->setEmail($email);
        return $user;
    }
}
