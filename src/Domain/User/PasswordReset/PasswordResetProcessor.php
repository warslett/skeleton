<?php

declare(strict_types=1);

namespace App\Domain\User\PasswordReset;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\User\PasswordReset\Event\PasswordResetEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordResetProcessor
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param User $user
     * @param PasswordResetToken $token
     * @return void
     */
    public function processPasswordReset(User $user, PasswordResetToken $token): void
    {
        /** @var string $plainPassword */
        $plainPassword = $user->getPlainPassword();
        $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
        $this->entityManager->flush();
        $user->eraseCredentials();
        $this->dispatcher->dispatch(new PasswordResetEvent($user, $token));
    }
}
