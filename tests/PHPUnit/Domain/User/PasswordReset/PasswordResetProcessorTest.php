<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\PasswordReset;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\User\PasswordReset\Event\PasswordResetEvent;
use App\Domain\User\PasswordReset\PasswordResetProcessor;
use App\Tests\PHPUnit\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Mock;
use Mockery as m;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordResetProcessorTest extends TestCase
{

    public function testProcessPasswordResetEncodesPassword(): void
    {
        $encoder = $this->mockEncoder('encodedPassword');
        $processor = new PasswordResetProcessor($encoder, $this->mockEntityManager(), $this->mockEventDispatcher());
        $plainPassword = 'Password1!';
        $user = $this->mockUser($plainPassword);

        $processor->processPasswordReset($user, $this->mockToken());

        $encoder->shouldHaveReceived('encodePassword')->once()->with($user, $plainPassword);
    }

    public function testProcessPasswordResetSetsPassword(): void
    {
        $encodedPassword = 't6y7u8i9o0p';
        $processor = new PasswordResetProcessor(
            $this->mockEncoder($encodedPassword),
            $this->mockEntityManager(),
            $this->mockEventDispatcher()
        );
        $user = $this->mockUser('Password1!');

        $processor->processPasswordReset($user, $this->mockToken());

        $user->shouldHaveReceived('setPassword')->once()->with($encodedPassword);
    }

    public function testProcessPasswordResetCallsFlush(): void
    {
        $entityManager = $this->mockEntityManager();
        $processor = new PasswordResetProcessor(
            $this->mockEncoder('encodedPassword'),
            $entityManager,
            $this->mockEventDispatcher()
        );

        $processor->processPasswordReset($this->mockUser('Password1!'), $this->mockToken());

        $entityManager->shouldHaveReceived('flush')->once();
    }

    public function testProcessPasswordErasesCredentials(): void
    {
        $encodedPassword = 't6y7u8i9o0p';
        $processor = new PasswordResetProcessor(
            $this->mockEncoder($encodedPassword),
            $this->mockEntityManager(),
            $this->mockEventDispatcher()
        );
        $user = $this->mockUser('Password1!');

        $processor->processPasswordReset($user, $this->mockToken());

        $user->shouldHaveReceived('eraseCredentials')->once();
    }

    public function testProcessPasswordDispatchesEvent(): void
    {
        $eventDispatcher = $this->mockEventDispatcher();
        $processor = new PasswordResetProcessor(
            $this->mockEncoder('encodedPassword'),
            $this->mockEntityManager(),
            $eventDispatcher
        );
        $user = $this->mockUser('Password1!');
        $token = $this->mockToken();

        $processor->processPasswordReset($user, $token);

        $eventDispatcher->shouldHaveReceived('dispatch')
            ->once()
            ->with(m::on(function (object $event) use ($user, $token): bool {
                $this->assertInstanceOf(PasswordResetEvent::class, $event);
                /** @var PasswordResetEvent $event */
                $this->assertSame($user, $event->getUser());
                $this->assertSame($token, $event->getToken());
                return true;
            }))
        ;
    }

    /**
     * @param string $encodedPassword
     * @return UserPasswordEncoderInterface&Mock
     */
    private function mockEncoder(string $encodedPassword): UserPasswordEncoderInterface
    {
        $encoder = m::mock(UserPasswordEncoderInterface::class);
        $encoder->shouldReceive('encodePassword')->andReturn($encodedPassword);
        return $encoder;
    }

    /**
     * @return EntityManagerInterface&Mock
     */
    private function mockEntityManager(): EntityManagerInterface
    {
        $entityManager = m::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('flush');
        return $entityManager;
    }

    /**
     * @param string $plainPassword
     * @return User&Mock
     */
    private function mockUser(string $plainPassword): User
    {
        $user = m::mock(User::class);
        $user->shouldReceive('getPlainPassword')->andReturn($plainPassword);
        $user->shouldReceive('setPassword');
        $user->shouldReceive('eraseCredentials');
        return $user;
    }

    /**
     * @return EventDispatcherInterface&Mock
     */
    private function mockEventDispatcher(): EventDispatcherInterface
    {
        $dispatcher = m::mock(EventDispatcherInterface::class);
        $dispatcher->shouldReceive('dispatch');
        return $dispatcher;
    }

    /**
     * @return PasswordResetToken&Mock
     */
    private function mockToken(): PasswordResetToken
    {
        return m::mock(PasswordResetToken::class);
    }
}
