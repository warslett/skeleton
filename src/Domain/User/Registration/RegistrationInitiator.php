<?php

declare(strict_types=1);

namespace App\Domain\User\Registration;

use App\Domain\Repository\UserRepository;
use App\Domain\User\Registration\Event\RegistrationInitiatedForExistingEmailEvent;
use App\Domain\User\Registration\Event\RegistrationTokenCreatedEvent;
use App\Domain\User\Registration\Factory\RegistrationTokenFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class RegistrationInitiator
{
    private UserRepository $userRepository;
    private EventDispatcherInterface $dispatcher;
    private RegistrationTokenFactory $registrationTokenFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository $userRepository,
        EventDispatcherInterface $dispatcher,
        RegistrationTokenFactory $registrationTokenFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->userRepository = $userRepository;
        $this->dispatcher = $dispatcher;
        $this->registrationTokenFactory = $registrationTokenFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $email
     * @return void
     */
    public function initiateRegistration(string $email): void
    {
        if ($this->userRepository->countByEmail($email) > 0) {
            $this->dispatcher->dispatch(new RegistrationInitiatedForExistingEmailEvent($email));
            return;
        }

        $token = $this->registrationTokenFactory->create($email);
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new RegistrationTokenCreatedEvent($token));
    }
}
