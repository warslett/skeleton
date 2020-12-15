<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\PasswordReset\Listener\PasswordReset;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\User\PasswordReset\Event\PasswordResetEvent;
use App\Domain\User\PasswordReset\Listener\PasswordReset\ExpirePasswordResetTokenListener;
use App\Factory\DateTimeFactory;
use App\Tests\PHPUnit\TestCase;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Mockery as m;
use Mockery\Mock;

class ExpirePasswordResetTokenListenerTest extends TestCase
{

    public function testInvokeExpiresPasswordResetToken(): void
    {
        $now = new DateTimeImmutable('2020-11-02T13:20:00.0000Z');
        $listener = new ExpirePasswordResetTokenListener(
            $this->mockDateTimeFactory($now),
            $this->mockEntityManager()
        );
        $token = $this->mockToken();

        $listener(new PasswordResetEvent(new User(), $token));

        $token->shouldHaveReceived('setExpiry')->once()->with($now);
    }

    public function testInvokeCallsFlush(): void
    {
        $entityManager = $this->mockEntityManager();
        $listener = new ExpirePasswordResetTokenListener(
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-02T13:20:00.0000Z')),
            $entityManager
        );

        $listener(new PasswordResetEvent(new User(), $this->mockToken()));

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
     * @return PasswordResetToken&Mock
     */
    private function mockToken(): PasswordResetToken
    {
        $token = m::mock(PasswordResetToken::class);
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
