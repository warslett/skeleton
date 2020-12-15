<?php

declare(strict_types=1);

namespace App\Domain\User\Registration\Factory;

use App\Domain\Entity\RegistrationToken;
use App\Factory\DateTimeFactory;
use App\Domain\Repository\RegistrationTokenRepository;
use RuntimeException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RegistrationTokenFactory
{
    private TokenGeneratorInterface $tokenGenerator;
    private RegistrationTokenRepository $repository;
    private DateTimeFactory $dateTimeFactory;

    public function __construct(
        TokenGeneratorInterface $tokenGenerator,
        RegistrationTokenRepository $repository,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->tokenGenerator = $tokenGenerator;
        $this->repository = $repository;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @param string $email
     * @return RegistrationToken
     */
    public function create(string $email): RegistrationToken
    {
        for ($x = 1; $x <= 100; $x++) {
            $token = $this->tokenGenerator->generateToken();
            if ($this->repository->countByToken($token) === 0) {
                return new RegistrationToken(
                    $token,
                    $email,
                    $this->dateTimeFactory->createModified('+1 day')
                );
            }
        }
        throw new RuntimeException("Failed to generate unique registration token");
    }
}
