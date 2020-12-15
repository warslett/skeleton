<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\Registration\Listener\RegistrationInitiatedForExistingEmailEvent;

use App\Domain\User\Registration\Event\RegistrationInitiatedForExistingEmailEvent;
use App\Domain\User\Registration\Listener\RegistrationInitiatedForExistingEmailEvent\SendNotificationListener;
use App\Mime\MessageFactory;
use App\Tests\PHPUnit\TestCase;
use Mockery\Mock;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Mockery as m;

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

        $listener(new RegistrationInitiatedForExistingEmailEvent('john@acme.co'));

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

        $listener(new RegistrationInitiatedForExistingEmailEvent('john@acme.co'));

        $message->shouldHaveReceived('subject')->once()->with("Your email address is already registered");
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

        $listener(new RegistrationInitiatedForExistingEmailEvent($email));

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

        $listener(new RegistrationInitiatedForExistingEmailEvent('john@acme.co'));

        $message->shouldHaveReceived('htmlTemplate')
            ->once()
            ->with('email/user/registration/email_already_registered.html.twig');
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

        $listener(new RegistrationInitiatedForExistingEmailEvent('john@acme.co'));

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
