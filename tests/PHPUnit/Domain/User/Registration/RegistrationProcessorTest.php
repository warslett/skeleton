<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\Registration;

use App\Domain\Entity\RegistrationToken;
use App\Domain\Entity\User;
use App\Domain\User\Registration\Event\RegistrationEvent;
use App\Domain\User\Registration\RegistrationProcessor;
use App\Tests\PHPUnit\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Mock;
use Mockery as m;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationProcessorTest extends TestCase
{

    public function testProcessRegistrationEncodesPassword(): void
    {
        $encoder = $this->mockEncoder('encodedPassword');
        $processor = new RegistrationProcessor(
            $encoder,
            $this->mockEntityManager(),
            $this->mockEventDispatcher()
        );
        $plainPassword = 'Password1!';
        $user = $this->mockUser($plainPassword);

        $processor->processRegistration($user, $this->mockToken());

        $encoder->shouldHaveReceived('encodePassword')->once()->with($user, $plainPassword);
    }

    public function testProcessRegistrationSetsPassword(): void
    {
        $encodedPassword = 't6y7u8i9o0p';
        $processor = new RegistrationProcessor(
            $this->mockEncoder($encodedPassword),
            $this->mockEntityManager(),
            $this->mockEventDispatcher()
        );
        $user = $this->mockUser('Password1!');

        $processor->processRegistration($user, $this->mockToken());

        $user->shouldHaveReceived('setPassword')->once()->with($encodedPassword);
    }

    public function testProcessRegistrationPersistsUser(): void
    {
        $entityManager = $this->mockEntityManager();
        $processor = new RegistrationProcessor(
            $this->mockEncoder('encodedPassword'),
            $entityManager,
            $this->mockEventDispatcher()
        );
        $user = $this->mockUser('Password1!');

        $processor->processRegistration($user, $this->mockToken());

        $entityManager->shouldHaveReceived('persist')->once()->with($user);
        $entityManager->shouldHaveReceived('flush')->once();
    }

    public function testProcessPasswordErasesCredentials(): void
    {
        $encodedPassword = 't6y7u8i9o0p';
        $processor = new RegistrationProcessor(
            $this->mockEncoder($encodedPassword),
            $this->mockEntityManager(),
            $this->mockEventDispatcher()
        );
        $user = $this->mockUser('Password1!');

        $processor->processRegistration($user, $this->mockToken());

        $user->shouldHaveReceived('eraseCredentials')->once();
    }

    public function testProcessPasswordDispatchesEvent(): void
    {
        $eventDispatcher = $this->mockEventDispatcher();
        $processor = new RegistrationProcessor(
            $this->mockEncoder('encodedPassword'),
            $this->mockEntityManager(),
            $eventDispatcher
        );
        $user = $this->mockUser('Password1!');
        $token = $this->mockToken();

        $processor->processRegistration($user, $token);

        $eventDispatcher->shouldHaveReceived('dispatch')
            ->once()
            ->with(m::on(function (object $event) use ($user, $token): bool {
                $this->assertInstanceOf(RegistrationEvent::class, $event);
                /** @var RegistrationEvent $event */
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
        $entityManager->shouldReceive('persist');
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
     * @return RegistrationToken&Mock
     */
    private function mockToken(): RegistrationToken
    {
        return m::mock(RegistrationToken::class);
    }
}
