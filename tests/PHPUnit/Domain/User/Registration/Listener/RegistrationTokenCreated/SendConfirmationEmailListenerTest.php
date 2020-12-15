<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\Registration\Listener\RegistrationTokenCreated;

use App\Domain\Entity\RegistrationToken;
use App\Domain\User\Registration\Event\RegistrationTokenCreatedEvent;
use App\Domain\User\Registration\Listener\RegistrationTokenCreated\SendConfirmationEmailListener;
use App\Mime\MessageFactory;
use App\Tests\PHPUnit\TestCase;
use Mockery\Mock;
use RuntimeException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Mockery as m;

class SendConfirmationEmailListenerTest extends TestCase
{

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeTokenNullThrowsException()
    {
        $listener = new SendConfirmationEmailListener(
            $this->mockMessageFactory($this->mockMessage()),
            $this->mockMailer()
        );

        $this->expectExceptionMessage(RuntimeException::class);
        $this->expectExceptionMessage("Couldn't get plaintext registration token");

        $listener(new RegistrationTokenCreatedEvent($this->mockToken('john@acme.co', null)));
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeCreatesMessage()
    {
        $factory = $this->mockMessageFactory($this->mockMessage());
        $listener = new SendConfirmationEmailListener($factory, $this->mockMailer());

        $listener(new RegistrationTokenCreatedEvent($this->mockToken('john@acme.co', 't6y7u8i9o0p')));

        $factory->shouldHaveReceived('createSystemEmail')->once();
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSetsSubjectOnMessage()
    {
        $message = $this->mockMessage();
        $listener = new SendConfirmationEmailListener($this->mockMessageFactory($message), $this->mockMailer());

        $listener(new RegistrationTokenCreatedEvent($this->mockToken('john@acme.co', 't6y7u8i9o0p')));

        $message->shouldHaveReceived('subject')->once()->with("Confirm your email address");
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSetsToOnMessage()
    {
        $message = $this->mockMessage();
        $listener = new SendConfirmationEmailListener($this->mockMessageFactory($message), $this->mockMailer());
        $email = 'john@acme.co';

        $listener(new RegistrationTokenCreatedEvent($this->mockToken($email, 't6y7u8i9o0p')));

        $message->shouldHaveReceived('to')->once()->with($email);
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSetsHtmlTemplateOnMessage()
    {
        $message = $this->mockMessage();
        $listener = new SendConfirmationEmailListener($this->mockMessageFactory($message), $this->mockMailer());

        $listener(new RegistrationTokenCreatedEvent($this->mockToken('john@acme.co', 't6y7u8i9o0p')));

        $message->shouldHaveReceived('htmlTemplate')
            ->once()
            ->with('email/user/registration/confirmation.html.twig');
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSetsContextOnMessage()
    {
        $message = $this->mockMessage();
        $listener = new SendConfirmationEmailListener($this->mockMessageFactory($message), $this->mockMailer());

        $plainText = 't6y7u8i9o0p';
        $listener(new RegistrationTokenCreatedEvent($this->mockToken('john@acme.co', $plainText)));

        $message->shouldHaveReceived('context')
            ->once()
            ->with(['token' => $plainText]);
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSendsMessage()
    {
        $message = $this->mockMessage();
        $mailer = $this->mockMailer();
        $listener = new SendConfirmationEmailListener($this->mockMessageFactory($message), $mailer);

        $listener(new RegistrationTokenCreatedEvent($this->mockToken('john@acme.co', 't6y7u8i9o0p')));

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
        $message->shouldReceive('context')->andReturnSelf();
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

    /**
     * @param string $email
     * @param string|null $plainTextToken
     * @return RegistrationToken&Mock
     */
    private function mockToken(string $email, ?string $plainTextToken): RegistrationToken
    {
        $token = m::mock(RegistrationToken::class);
        $token->shouldReceive('getEmail')->andReturn($email);
        $token->shouldReceive('getTokenPlainText')->andReturn($plainTextToken);
        return $token;
    }
}
