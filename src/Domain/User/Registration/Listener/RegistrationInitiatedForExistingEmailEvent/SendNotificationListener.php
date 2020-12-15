<?php

declare(strict_types=1);

namespace App\Domain\User\Registration\Listener\RegistrationInitiatedForExistingEmailEvent;

use App\Domain\User\Registration\Event\RegistrationInitiatedForExistingEmailEvent;
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
     * @param RegistrationInitiatedForExistingEmailEvent $event
     * @return void
     * @throws TransportExceptionInterface
     */
    public function __invoke(RegistrationInitiatedForExistingEmailEvent $event): void
    {
        $this->mailer->send($this->messageFactory->createSystemEmail()
            ->subject("Your email address is already registered")
            ->to($event->getEmail())
            ->htmlTemplate('email/user/registration/email_already_registered.html.twig'));
    }
}
