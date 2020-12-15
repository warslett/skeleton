<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\PasswordReset;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepository;
use App\Domain\User\PasswordReset\Event\PasswordResetInitiatedForNonExistentUserEvent;
use App\Domain\User\PasswordReset\Event\PasswordResetTokenCreatedEvent;
use App\Domain\User\PasswordReset\Factory\PasswordResetTokenFactory;
use App\Domain\User\PasswordReset\PasswordResetInitiator;
use App\Tests\PHPUnit\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Mock;
use Mockery as m;
use Psr\EventDispatcher\EventDispatcherInterface;

class PasswordResetInitiatorTest extends TestCase
{

    public function testInitiatePasswordResetFindsUserForEmail()
    {
        $userRepository = $this->mockUserRepository($this->mockUser());
        $initiator = new PasswordResetInitiator(
            $userRepository,
            $this->mockEventDispatcher(),
            $this->mockTokenFactory($this->mockToken()),
            $this->mockEntityManager()
        );
        $email = 'john@amce.co';

        $initiator->initiatePasswordReset($email);

        $userRepository->shouldHaveReceived('findOneByEmail')->once()->with($email);
    }

    public function testInitiateUserDoesNotExistDispatchesUserNotFoundEvent()
    {
        $dispatcher = $this->mockEventDispatcher();
        $initiator = new PasswordResetInitiator(
            $this->mockUserRepository(null),
            $dispatcher,
            $this->mockTokenFactory(),
            $this->mockEntityManager()
        );
        $email = 'john@amce.co';

        $initiator->initiatePasswordReset($email);

        $dispatcher->shouldHaveReceived('dispatch')->once()->withArgs(function (object $event) use ($email): bool {
            $this->assertInstanceOf(PasswordResetInitiatedForNonExistentUserEvent::class, $event);
            /** @var PasswordResetInitiatedForNonExistentUserEvent $event */
            $this->assertSame($email, $event->getEmail());
            return true;
        });
    }

    public function testInitiateUserDoesNotExistDoesNotCreateToken()
    {
        $tokenFactory = $this->mockTokenFactory($this->mockToken());
        $initiator = new PasswordResetInitiator(
            $this->mockUserRepository(null),
            $this->mockEventDispatcher(),
            $tokenFactory,
            $this->mockEntityManager()
        );

        $initiator->initiatePasswordReset('john@amce.co');

        $tokenFactory->shouldNotHaveReceived('create');
    }

    public function testInitiateUserExistsCreatesToken()
    {
        $user = $this->mockUser();
        $tokenFactory = $this->mockTokenFactory($this->mockToken());
        $initiator = new PasswordResetInitiator(
            $this->mockUserRepository($user),
            $this->mockEventDispatcher(),
            $tokenFactory,
            $this->mockEntityManager()
        );

        $initiator->initiatePasswordReset('john@amce.co');

        $tokenFactory->shouldHaveReceived('create')->once()->with($user);
    }

    public function testInitiateEmailAvailablePersistsToken()
    {
        $token = $this->mockToken();
        $entityManager = $this->mockEntityManager();
        $initiator = new PasswordResetInitiator(
            $this->mockUserRepository($this->mockUser()),
            $this->mockEventDispatcher(),
            $this->mockTokenFactory($token),
            $entityManager
        );

        $initiator->initiatePasswordReset('john@amce.co');

        $entityManager->shouldHaveReceived('persist')->once()->with($token);
        $entityManager->shouldHaveReceived('flush')->once();
    }

    public function testInitiateUserExistsDispatchesEvent()
    {
        $token = $this->mockToken();
        $dispatcher = $this->mockEventDispatcher();
        $initiator = new PasswordResetInitiator(
            $this->mockUserRepository($this->mockUser()),
            $dispatcher,
            $this->mockTokenFactory($token),
            $this->mockEntityManager()
        );

        $initiator->initiatePasswordReset('john@acme.co');

        $dispatcher->shouldHaveReceived('dispatch')->once()->withArgs(function (object $event) use ($token): bool {
            $this->assertInstanceOf(PasswordResetTokenCreatedEvent::class, $event);
            /** @var PasswordResetTokenCreatedEvent $event */
            $this->assertSame($token, $event->getToken());
            return true;
        });
    }

    /**
     * @param User|null $user
     * @return UserRepository&Mock
     */
    private function mockUserRepository(?User $user): UserRepository
    {
        $repository = m::mock(UserRepository::class);
        $repository->shouldReceive('findOneByEmail')->andReturn($user);
        return $repository;
    }

    /**
     * @return User&Mock
     */
    private function mockUser(): User
    {
        return m::mock(User::class);
    }

    /**
     * @param PasswordResetToken|null $token
     * @return PasswordResetTokenFactory&Mock
     */
    private function mockTokenFactory(?PasswordResetToken $token = null): PasswordResetTokenFactory
    {
        $tokenFactory = m::mock(PasswordResetTokenFactory::class);
        if (null !== $token) {
            $tokenFactory->shouldReceive('create')->andReturn($token);
        }
        return $tokenFactory;
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
     * @return EventDispatcherInterface&Mock
     */
    private function mockEventDispatcher(): EventDispatcherInterface
    {
        $eventDispatcher = m::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');
        return $eventDispatcher;
    }

    /**
     * @return PasswordResetToken&Mock
     */
    private function mockToken(): PasswordResetToken
    {
        return m::mock(PasswordResetToken::class);
    }
}
