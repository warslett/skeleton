<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\Registration;

use App\Domain\Entity\RegistrationToken;
use App\Domain\Repository\UserRepository;
use App\Domain\User\Registration\Event\RegistrationInitiatedForExistingEmailEvent;
use App\Domain\User\Registration\Event\RegistrationTokenCreatedEvent;
use App\Domain\User\Registration\Factory\RegistrationTokenFactory;
use App\Domain\User\Registration\RegistrationInitiator;
use App\Tests\PHPUnit\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery as m;
use Mockery\Mock;
use Psr\EventDispatcher\EventDispatcherInterface;

class RegistrationInitiatorTest extends TestCase
{
    public function testInitiateRegistrationCountsUsersForEmail()
    {
        $userRepository = $this->mockUserRepository(1);
        $initiator = new RegistrationInitiator(
            $userRepository,
            $this->mockEventDispatcher(),
            $this->mockTokenFactory(),
            $this->mockEntityManager()
        );
        $email = 'john@amce.co';

        $initiator->initiateRegistration($email);

        $userRepository->shouldHaveReceived('countByEmail')->once()->with($email);
    }

    public function testInitiateEmailAlreadyRegisteredDispatchesEvent()
    {
        $dispatcher = $this->mockEventDispatcher();

        $initiator = new RegistrationInitiator(
            $this->mockUserRepository(1),
            $dispatcher,
            $this->mockTokenFactory(),
            $this->mockEntityManager()
        );
        $email = 'john@amce.co';

        $initiator->initiateRegistration($email);

        $dispatcher->shouldHaveReceived('dispatch')->once()->withArgs(function (object $event) use ($email): bool {
            $this->assertInstanceOf(RegistrationInitiatedForExistingEmailEvent::class, $event);
            /** @var RegistrationInitiatedForExistingEmailEvent $event */
            $this->assertSame($email, $event->getEmail());
            return true;
        });
    }

    public function testInitiateEmailAvailableCreatesToken()
    {
        $tokenFactory = $this->mockTokenFactory($this->mockToken());
        $initiator = new RegistrationInitiator(
            $this->mockUserRepository(0),
            $this->mockEventDispatcher(),
            $tokenFactory,
            $this->mockEntityManager()
        );
        $email = 'john@amce.co';

        $initiator->initiateRegistration($email);

        $tokenFactory->shouldHaveReceived('create')->once()->with($email);
    }

    public function testInitiateEmailAvailablePersistsToken()
    {
        $token = $this->mockToken();
        $entityManager = $this->mockEntityManager();
        $initiator = new RegistrationInitiator(
            $this->mockUserRepository(0),
            $this->mockEventDispatcher(),
            $this->mockTokenFactory($token),
            $entityManager
        );

        $initiator->initiateRegistration('john@amce.co');

        $entityManager->shouldHaveReceived('persist')->once()->with($token);
        $entityManager->shouldHaveReceived('flush')->once();
    }

    public function testInitiateEmailAvailableDispatchesEvent()
    {
        $token = $this->mockToken();
        $dispatcher = $this->mockEventDispatcher();
        $initiator = new RegistrationInitiator(
            $this->mockUserRepository(0),
            $dispatcher,
            $this->mockTokenFactory($token),
            $this->mockEntityManager()
        );

        $initiator->initiateRegistration('john@acme.co');

        $dispatcher->shouldHaveReceived('dispatch')->once()->withArgs(function (object $event) use ($token): bool {
            $this->assertInstanceOf(RegistrationTokenCreatedEvent::class, $event);
            /** @var RegistrationTokenCreatedEvent $event */
            $this->assertSame($token, $event->getToken());
            return true;
        });
    }

    /**
     * @param int $count
     * @return UserRepository&Mock
     */
    private function mockUserRepository(int $count): UserRepository
    {
        $repository = m::mock(UserRepository::class);
        $repository->shouldReceive('countByEmail')->andReturn($count);
        return $repository;
    }

    /**
     * @param RegistrationToken|null $token
     * @return RegistrationTokenFactory&Mock
     */
    private function mockTokenFactory(?RegistrationToken $token = null): RegistrationTokenFactory
    {
        $tokenFactory = m::mock(RegistrationTokenFactory::class);
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
     * @return RegistrationToken&Mock
     */
    private function mockToken(): RegistrationToken
    {
        return m::mock(RegistrationToken::class);
    }
}
