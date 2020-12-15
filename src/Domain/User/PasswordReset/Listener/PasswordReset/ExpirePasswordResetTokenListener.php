<?php

declare(strict_types=1);

namespace App\Domain\User\PasswordReset\Listener\PasswordReset;

use App\Domain\User\PasswordReset\Event\PasswordResetEvent;
use App\Factory\DateTimeFactory;
use Doctrine\ORM\EntityManagerInterface;

class ExpirePasswordResetTokenListener
{
    private DateTimeFactory $dateTimeFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(DateTimeFactory $dateTimeFactory, EntityManagerInterface $entityManager)
    {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * @param PasswordResetEvent $event
     * @return void
     */
    public function __invoke(PasswordResetEvent $event): void
    {
        $token = $event->getToken();
        $token->setExpiry($this->dateTimeFactory->createNow());
        $this->entityManager->flush();
    }
}
