<?php

declare(strict_types=1);

namespace App\Domain\User\PasswordReset\Listener\PasswordResetInitiatedForNonExistentUser;

use App\Domain\User\PasswordReset\Event\PasswordResetInitiatedForNonExistentUserEvent;
use App\Mime\MessageFactory;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class SendNotificationListener
{
    private MessageFactory $messageFactory;
    private MailerInterface $mailer;

    public function __construct(MessageFactory $messageFactory, MailerInterface $mailer)
    {
        $this->messageFactory = $messageFactory;
        $this->mailer = $mailer;
    }

    /**
     * @param PasswordResetInitiatedForNonExistentUserEvent $event
     * @return void
     * @throws TransportExceptionInterface
     */
    public function __invoke(PasswordResetInitiatedForNonExistentUserEvent $event)
    {
        $this->mailer->send($this->messageFactory->createSystemEmail()
            ->subject("Your email address is not registered")
            ->to($event->getEmail())
            ->htmlTemplate('email/user/password_reset/address_not_found.html.twig'));
    }
}
