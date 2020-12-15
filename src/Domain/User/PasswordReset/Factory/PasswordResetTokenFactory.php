<?php

declare(strict_types=1);

namespace App\Domain\User\PasswordReset\Factory;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\Repository\PasswordResetTokenRepository;
use App\Factory\DateTimeFactory;
use RuntimeException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class PasswordResetTokenFactory
{
    private TokenGeneratorInterface $tokenGenerator;
    private PasswordResetTokenRepository $repository;
    private DateTimeFactory $dateTimeFactory;

    public function __construct(
        TokenGeneratorInterface $tokenGenerator,
        PasswordResetTokenRepository $repository,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->tokenGenerator = $tokenGenerator;
        $this->repository = $repository;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @param User $user
     * @return PasswordResetToken
     */
    public function create(User $user): PasswordResetToken
    {
        for ($x = 1; $x <= 100; $x++) {
            $token = $this->tokenGenerator->generateToken();
            if ($this->repository->countByToken($token) === 0) {
                return new PasswordResetToken($token, $user, $this->dateTimeFactory->createModified("+1 day"));
            }
        }
        throw new RuntimeException("Failed to generate unique password reset token");
    }
}
