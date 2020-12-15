<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\PasswordReset\Listener\PasswordResetTokenCreated;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\User\PasswordReset\Event\PasswordResetTokenCreatedEvent;
use App\Domain\User\PasswordReset\Listener\PasswordResetTokenCreated\SendPasswordResetEmailListener;
use App\Mime\MessageFactory;
use App\Tests\PHPUnit\TestCase;
use Mockery\Mock;
use Mockery as m;
use RuntimeException;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig;

class SendPasswordResetEmailListenerTest extends TestCase
{

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeTokenNullThrowsException()
    {
        $listener = new SendPasswordResetEmailListener(
            $this->mockMessageFactory($this->mockMessage()),
            $this->mockMailer()
        );

        $this->expectExceptionMessage(RuntimeException::class);
        $this->expectExceptionMessage("Couldn't get plaintext password reset token");

        $listener(new PasswordResetTokenCreatedEvent(
            $this->mockToken($this->mockUser('john@acme.co'), null)
        ));
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeCreatesMessage()
    {
        $factory = $this->mockMessageFactory($this->mockMessage());
        $listener = new SendPasswordResetEmailListener($factory, $this->mockMailer());

        $listener(new PasswordResetTokenCreatedEvent(
            $this->mockToken($this->mockUser('john@acme.co'), 't6y7u8i9o0p')
        ));

        $factory->shouldHaveReceived('createSystemEmail')->once();
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSetsSubjectOnMessage()
    {
        $message = $this->mockMessage();
        $listener = new SendPasswordResetEmailListener($this->mockMessageFactory($message), $this->mockMailer());

        $listener(new PasswordResetTokenCreatedEvent(
            $this->mockToken($this->mockUser('john@acme.co'), 't6y7u8i9o0p')
        ));

        $message->shouldHaveReceived('subject')->once()->with("Reset your password");
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSetsToOnMessage()
    {
        $message = $this->mockMessage();
        $listener = new SendPasswordResetEmailListener($this->mockMessageFactory($message), $this->mockMailer());
        $email = 'john@acme.co';

        $listener(new PasswordResetTokenCreatedEvent(
            $this->mockToken($this->mockUser($email), 't6y7u8i9o0p')
        ));

        $message->shouldHaveReceived('to')->once()->with($email);
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSetsHtmlTemplateOnMessage()
    {
        $message = $this->mockMessage();
        $listener = new SendPasswordResetEmailListener($this->mockMessageFactory($message), $this->mockMailer());

        $listener(new PasswordResetTokenCreatedEvent(
            $this->mockToken($this->mockUser('john@acme.co'), 't6y7u8i9o0p')
        ));

        $message->shouldHaveReceived('htmlTemplate')
            ->once()
            ->with('email/user/password_reset/password_reset.html.twig');
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testInvokeSetsContextOnMessage()
    {
        $message = $this->mockMessage();
        $listener = new SendPasswordResetEmailListener($this->mockMessageFactory($message), $this->mockMailer());
        $plainText = 't6y7u8i9o0p';

        $listener(new PasswordResetTokenCreatedEvent(
            $this->mockToken($this->mockUser('john@acme.co'), $plainText)
        ));

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
        $listener = new SendPasswordResetEmailListener($this->mockMessageFactory($message), $mailer);

        $listener(new PasswordResetTokenCreatedEvent(
            $this->mockToken($this->mockUser('john@acme.co'), 't6y7u8i9o0p')
        ));

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
     * @return User&Mock
     */
    private function mockUser(string $email): User
    {
        $user = m::mock(User::class);
        $user->shouldReceive('getEmail')->andReturn($email);
        return $user;
    }

    /**
     * @param User $user
     * @param string|null $tokenPlainText
     * @return PasswordResetToken&Mock
     */
    private function mockToken(User $user, ?string $tokenPlainText): PasswordResetToken
    {
        $token = m::mock(PasswordResetToken::class);
        $token->shouldReceive('getUser')->andReturn($user);
        $token->shouldReceive('getTokenPlainText')->andReturn($tokenPlainText);
        return $token;
    }
}
