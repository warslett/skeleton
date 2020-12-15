<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\Registration\Listener\Registration;

use App\Domain\Entity\RegistrationToken;
use App\Domain\Entity\User;
use App\Domain\User\Registration\Event\RegistrationEvent;
use App\Domain\User\Registration\Listener\Registration\ExpireRegistrationTokenListener;
use App\Factory\DateTimeFactory;
use App\Tests\PHPUnit\TestCase;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Mockery as m;
use Mockery\Mock;

class ExpireRegistrationTokenListenerTest extends TestCase
{

    public function testInvokeExpiresRegistrationToken(): void
    {
        $now = new DateTimeImmutable('2020-11-02T13:20:00.0000Z');
        $listener = new ExpireRegistrationTokenListener(
            $this->mockDateTimeFactory($now),
            $this->mockEntityManager()
        );
        $token = $this->mockToken();

        $listener(new RegistrationEvent(new User(), $token));

        $token->shouldHaveReceived('setExpiry')->once()->with($now);
    }

    public function testInvokeCallsFlush(): void
    {
        $entityManager = $this->mockEntityManager();
        $listener = new ExpireRegistrationTokenListener(
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-02T13:20:00.0000Z')),
            $entityManager
        );

        $listener(new RegistrationEvent(new User(), $this->mockToken()));

        $entityManager->shouldHaveReceived('flush')->once();
    }

    /**
     * @param DateTimeImmutable $now
     * @return DateTimeFactory
     */
    private function mockDateTimeFactory(DateTimeImmutable $now): DateTimeFactory
    {
        $factory = m::mock(DateTimeFactory::class);
        $factory->shouldReceive('createNow')->andReturn($now);
        return $factory;
    }

    /**
     * @return RegistrationToken&Mock
     */
    private function mockToken(): RegistrationToken
    {
        $token = m::mock(RegistrationToken::class);
        $token->shouldReceive('setExpiry');
        return $token;
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
}
