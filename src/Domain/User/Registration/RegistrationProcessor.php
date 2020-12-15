<?php

declare(strict_types=1);

namespace App\Domain\User\Registration;

use App\Domain\Entity\RegistrationToken;
use App\Domain\Entity\User;
use App\Domain\User\Registration\Event\RegistrationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationProcessor
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param User $user
     * @param RegistrationToken $token
     * @return void
     */
    public function processRegistration(User $user, RegistrationToken $token): void
    {
        /** @var string $plainPassword */
        $plainPassword = $user->getPlainPassword();
        $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $user->eraseCredentials();
        $this->eventDispatcher->dispatch(new RegistrationEvent($user, $token));
    }
}
