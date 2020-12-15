<?php

declare(strict_types=1);

namespace App\Domain\User\PasswordReset;

use App\Domain\Repository\UserRepository;
use App\Domain\User\PasswordReset\Event\PasswordResetInitiatedForNonExistentUserEvent;
use App\Domain\User\PasswordReset\Event\PasswordResetTokenCreatedEvent;
use App\Domain\User\PasswordReset\Factory\PasswordResetTokenFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class PasswordResetInitiator
{
    private UserRepository $userRepository;
    private EventDispatcherInterface $dispatcher;
    private PasswordResetTokenFactory $passwordResetTokenFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository $userRepository,
        EventDispatcherInterface $dispatcher,
        PasswordResetTokenFactory $passwordResetTokenFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->userRepository = $userRepository;
        $this->dispatcher = $dispatcher;
        $this->passwordResetTokenFactory = $passwordResetTokenFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * @todo implement this
     * @param string $email
     * @return void
     */
    public function initiatePasswordReset(string $email): void
    {
        $user = $this->userRepository->findOneByEmail($email);

        if (null === $user) {
            $this->dispatcher->dispatch(new PasswordResetInitiatedForNonExistentUserEvent($email));
            return;
        }

        $token = $this->passwordResetTokenFactory->create($user);
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new PasswordResetTokenCreatedEvent($token));
    }
}
