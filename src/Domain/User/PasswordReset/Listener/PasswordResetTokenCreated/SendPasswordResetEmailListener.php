<?php

declare(strict_types=1);

namespace App\Domain\User\PasswordReset\Listener\PasswordResetTokenCreated;

use App\Domain\User\PasswordReset\Event\PasswordResetTokenCreatedEvent;
use App\Mime\MessageFactory;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class SendPasswordResetEmailListener
{
    private MessageFactory $messageFactory;
    private MailerInterface $mailer;

    public function __construct(MessageFactory $messageFactory, MailerInterface $mailer)
    {
        $this->messageFactory = $messageFactory;
        $this->mailer = $mailer;
    }

    /**
     * @param PasswordResetTokenCreatedEvent $event
     * @return void
     * @throws TransportExceptionInterface
     */
    public function __invoke(PasswordResetTokenCreatedEvent $event): void
    {
        $tokenPlainText = $event->getToken()->getTokenPlainText();
        if (null === $tokenPlainText) {
            throw new RuntimeException("Couldn't get plaintext password reset token");
        }

        /** @var string $tokenUserEmail */
        $tokenUserEmail = $event->getToken()->getUser()->getEmail();

        $this->mailer->send($this->messageFactory->createSystemEmail()
            ->to($tokenUserEmail)
            ->subject("Reset your password")
            ->htmlTemplate('email/user/password_reset/password_reset.html.twig')
            ->context(['token' => $tokenPlainText]));
    }
}
