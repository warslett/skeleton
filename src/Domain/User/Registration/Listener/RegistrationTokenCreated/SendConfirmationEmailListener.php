<?php

declare(strict_types=1);

namespace App\Domain\User\Registration\Listener\RegistrationTokenCreated;

use App\Domain\User\Registration\Event\RegistrationTokenCreatedEvent;
use App\Mime\MessageFactory;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class SendConfirmationEmailListener
{
    private MessageFactory $messageFactory;
    private MailerInterface $mailer;

    public function __construct(MessageFactory $messageFactory, MailerInterface $mailer)
    {
        $this->messageFactory = $messageFactory;
        $this->mailer = $mailer;
    }

    /**
     * @param RegistrationTokenCreatedEvent $event
     * @return void
     * @throws TransportExceptionInterface
     */
    public function __invoke(RegistrationTokenCreatedEvent $event): void
    {
        $token = $event->getToken();
        $tokenPlainText = $token->getTokenPlainText();
        if (null === $tokenPlainText) {
            throw new RuntimeException("Couldn't get plaintext registration token");
        }

        $this->mailer->send($this->messageFactory->createSystemEmail()
            ->subject("Confirm your email address")
            ->to($token->getEmail())
            ->htmlTemplate('email/user/registration/confirmation.html.twig')
            ->context(['token' => $tokenPlainText]));
    }
}
