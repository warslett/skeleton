<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\PasswordReset\Resolver;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\Repository\PasswordResetTokenRepository;
use App\Domain\User\PasswordReset\Exception\PasswordResetTokenExpiredException;
use App\Domain\User\PasswordReset\Resolver\UserResolver;
use App\Factory\DateTimeFactory;
use App\Tests\PHPUnit\TestCase;
use DateTimeImmutable;
use Mockery\Mock;
use Mockery as m;

class UserResolverTest extends TestCase
{

    /**
     * @return void
     */
    public function testResolveFromTokenStringTokenExpiredThrowsException(): void
    {
        $resolver = new UserResolver(
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-01 16:05'))
        );

        $this->expectException(PasswordResetTokenExpiredException::class);
        $this->expectExceptionMessage("Your password reset link has expired, please try again");

        $resolver->resolveFromToken($this->mockToken(
            $this->mockUser(),
            new DateTimeImmutable('2020-11-01 16:00')
        ));
    }

    /**
     * @return void
     * @throws PasswordResetTokenExpiredException
     */
    public function testResolveFromTokenStringReturnsTokenUser(): void
    {
        $user = $this->mockUser();
        $resolver = new UserResolver(
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-01 16:05'))
        );

        $result = $resolver->resolveFromToken($this->mockToken(
            $user,
            new DateTimeImmutable('2020-11-02 16:00')
        ));

        $this->assertSame($user, $result);
    }

    /**
     * @return User&Mock
     */
    private function mockUser(): User
    {
        return m::mock(User::class);
    }

    /**
     * @param User $user
     * @param DateTimeImmutable $expiry
     * @return PasswordResetToken&Mock
     */
    private function mockToken(User $user, DateTimeImmutable $expiry): PasswordResetToken
    {
        $token = m::mock(PasswordResetToken::class);
        $token->shouldReceive('getUser')->andReturn($user);
        $token->shouldReceive('getExpiry')->andReturn($expiry);
        return $token;
    }

    /**
     * @param DateTimeImmutable $now
     * @return DateTimeFactory&Mock
     */
    private function mockDateTimeFactory(DateTimeImmutable $now): DateTimeFactory
    {
        $factory = m::mock(DateTimeFactory::class);
        $factory->shouldReceive('createNow')->andReturn($now);
        return $factory;
    }

    /**
     * @param PasswordResetToken|null $token
     * @return PasswordResetTokenRepository&Mock
     */
    private function mockPasswordResetTokenRepository(?PasswordResetToken $token): PasswordResetTokenRepository
    {
        $repository = m::mock(PasswordResetTokenRepository::class);
        $repository->shouldReceive('findOneByToken')->andReturn($token);
        return $repository;
    }
}
