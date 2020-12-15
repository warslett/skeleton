<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\PasswordReset\Listener\PasswordResetInitiatedForNonExistentUser;

use App\Domain\User\PasswordReset\Event\PasswordResetInitiatedForNonExistentUserEvent;
use App\Domain\User\PasswordReset\Listener\PasswordResetInitiatedForNonExistentUser\SendNotificationListener;
use App\Mime\MessageFactory;
use App\Tests\PHPUnit\TestCase;
use Mockery\Mock;
use Mockery as m;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class SendNotificationListenerTest extends TestCase
{

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeCreatesMessage()
    {
        $factory = $this->mockMessageFactory($this->mockMessage());
        $listener = new SendNotificationListener($factory, $this->mockMailer());

        $listener(new PasswordResetInitiatedForNonExistentUserEvent('john@acme.co'));

        $factory->shouldHaveReceived('createSystemEmail')->once();
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSetsSubjectOnMessage()
    {
        $message = $this->mockMessage();
        $listener = new SendNotificationListener($this->mockMessageFactory($message), $this->mockMailer());

        $listener(new PasswordResetInitiatedForNonExistentUserEvent('john@acme.co'));

        $message->shouldHaveReceived('subject')->once()->with("Your email address is not registered");
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSetsToOnMessage()
    {
        $message = $this->mockMessage();
        $listener = new SendNotificationListener($this->mockMessageFactory($message), $this->mockMailer());
        $email = 'john@acme.co';

        $listener(new PasswordResetInitiatedForNonExistentUserEvent($email));

        $message->shouldHaveReceived('to')->once()->with($email);
    }


    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSetsHtmlTemplateOnMessage()
    {
        $message = $this->mockMessage();
        $listener = new SendNotificationListener($this->mockMessageFactory($message), $this->mockMailer());

        $listener(new PasswordResetInitiatedForNonExistentUserEvent('john@acme.co'));

        $message->shouldHaveReceived('htmlTemplate')
            ->once()
            ->with('email/user/password_reset/address_not_found.html.twig');
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSendsMessage()
    {
        $message = $this->mockMessage();
        $mailer = $this->mockMailer();
        $listener = new SendNotificationListener($this->mockMessageFactory($message), $mailer);

        $listener(new PasswordResetInitiatedForNonExistentUserEvent('john@acme.co'));

        $mailer->shouldHaveReceived('send')->once()->with($message);
    }

    /**
     * @param TemplatedEmail $email
     * @return MessageFactory&Mock
     */
    private function mockMessageFactory(TemplatedEmail $email): MessageFactory
    {
        $factory = m::mock(MessageFactory::class);
        $factory->shouldReceive('createSystemEmail')->andReturn($email);
        return $factory;
    }

    /**
     * @return TemplatedEmail&Mock
     */
    private function mockMessage(): TemplatedEmail
    {
        $message = m::mock(TemplatedEmail::class);
        $message->shouldReceive('subject')->andReturnSelf();
        $message->shouldReceive('to')->andReturnSelf();
        $message->shouldReceive('htmlTemplate')->andReturnSelf();
        return $message;
    }

    /**
     * @return MailerInterface&Mock
     */
    private function mockMailer(): MailerInterface
    {
        $mailer = m::mock(MailerInterface::class);
        $mailer->shouldReceive('send');
        return $mailer;
    }
}
