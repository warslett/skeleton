<?php

declare(strict_types=1);

namespace App\Domain\User\Registration\Listener\Registration;

use App\Domain\User\Registration\Event\RegistrationEvent;
use App\Factory\DateTimeFactory;
use Doctrine\ORM\EntityManagerInterface;

class ExpireRegistrationTokenListener
{
    private DateTimeFactory $dateTimeFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(DateTimeFactory $dateTimeFactory, EntityManagerInterface $entityManager)
    {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * @param RegistrationEvent $event
     * @return void
     */
    public function __invoke(RegistrationEvent $event): void
    {
        $token = $event->getToken();
        $token->setExpiry($this->dateTimeFactory->createNow());
        $this->entityManager->flush();
    }
}
