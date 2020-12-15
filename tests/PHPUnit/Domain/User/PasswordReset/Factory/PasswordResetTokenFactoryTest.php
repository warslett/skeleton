<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\PasswordReset\Factory;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\Repository\PasswordResetTokenRepository;
use App\Domain\User\PasswordReset\Factory\PasswordResetTokenFactory;
use App\Factory\DateTimeFactory;
use App\Tests\PHPUnit\TestCase;
use DateTimeImmutable;
use Mockery\Mock;
use Mockery as m;
use RuntimeException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class PasswordResetTokenFactoryTest extends TestCase
{

    public function testCreateGeneratesToken(): void
    {
        $tokenGenerator = $this->mockTokenGenerator('foo');
        $registrationTokenFactory = new PasswordResetTokenFactory(
            $tokenGenerator,
            $this->mockTokenRepository(0),
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-02T16:00:00.0000Z')),
        );

        $registrationTokenFactory->create($this->mockUser());

        $tokenGenerator->shouldHaveReceived('generateToken')->once();
    }

    public function testCreateCountsPasswordResetTokensForTokenString(): void
    {
        $tokenString = 'foo';
        $tokenRepository = $this->mockTokenRepository(0);
        $registrationTokenFactory = new PasswordResetTokenFactory(
            $this->mockTokenGenerator($tokenString),
            $tokenRepository,
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-02T16:00:00.0000Z')),
        );

        $registrationTokenFactory->create($this->mockUser());

        $tokenRepository->shouldHaveReceived('countByToken')->once()->with($tokenString);
    }

    public function testCreateCreatesExpiryDate(): void
    {
        $dateTimeFactory = $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-02T16:00:00.0000Z'));
        $registrationTokenFactory = new PasswordResetTokenFactory(
            $this->mockTokenGenerator('foo'),
            $this->mockTokenRepository(0),
            $dateTimeFactory,
        );

        $registrationTokenFactory->create($this->mockUser());

        $dateTimeFactory->shouldHaveReceived('createModified')->once()->with('+1 day');
    }

    public function testCreateReturnsPasswordResetToken(): void
    {
        $token = 'foo';
        $user = $this->mockUser();
        $expiry = new DateTimeImmutable('2020-11-02T16:00:00.0000Z');
        $registrationTokenFactory = new PasswordResetTokenFactory(
            $this->mockTokenGenerator($token),
            $this->mockTokenRepository(0),
            $this->mockDateTimeFactory($expiry)
        );

        $registrationToken = $registrationTokenFactory->create($user);

        $this->assertSame(
            hash(PasswordResetToken::TOKEN_HASHING_ALGORITHM, $token),
            $registrationToken->getHashedToken()
        );
        $this->assertSame($user, $registrationToken->getUser());
        $this->assertSame($expiry, $registrationToken->getExpiry());
    }

    public function testCreateGeneratedTokenAlreadyInUseGeneratesAnotherToken(): void
    {
        $tokenGenerator = $this->mockTokenGenerator('foo', 'bar');
        $tokenRepository = $this->mockTokenRepository(1, 0);
        $registrationTokenFactory = new PasswordResetTokenFactory(
            $tokenGenerator,
            $tokenRepository,
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-02T16:00:00.0000Z'))
        );

        $registrationTokenFactory->create($this->mockUser());

        $tokenGenerator->shouldHaveReceived('generateToken')->twice();
        $tokenRepository->shouldHaveReceived('countByToken')->twice();
    }

    public function testCreateGeneratedTokenAlreadyInUseReturnsSecondToken(): void
    {
        $token = 'bar';
        $user = $this->mockUser();
        $expiry = new DateTimeImmutable('2020-11-02T16:00:00.0000Z');
        $registrationTokenFactory = new PasswordResetTokenFactory(
            $this->mockTokenGenerator('foo', $token),
            $this->mockTokenRepository(1, 0),
            $this->mockDateTimeFactory($expiry)
        );

        $registrationToken = $registrationTokenFactory->create($user);

        $this->assertSame(
            hash(PasswordResetToken::TOKEN_HASHING_ALGORITHM, $token),
            $registrationToken->getHashedToken()
        );
        $this->assertSame($user, $registrationToken->getUser());
        $this->assertSame($expiry, $registrationToken->getExpiry());
    }

    public function testCreateFailsToGenerateUniquePasswordResetTokenThrowsException(): void
    {
        $expiry = new DateTimeImmutable('2020-11-02T16:00:00.0000Z');
        $registrationTokenFactory = new PasswordResetTokenFactory(
            $this->mockInfiniteTokenGenerator(),
            $this->mockTokenRepositoryNeverUnique(),
            $this->mockDateTimeFactory($expiry)
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed to generate unique password reset token");

        $registrationTokenFactory->create($this->mockUser());
    }

    /**
     * @param string ...$tokens
     * @return TokenGeneratorInterface&Mock
     */
    private function mockTokenGenerator(string ...$tokens): TokenGeneratorInterface
    {
        $tokenGenerator = m::mock(TokenGeneratorInterface::class);
        $tokenGenerator->shouldReceive('generateToken')->andReturn(...$tokens);
        return $tokenGenerator;
    }

    /**
     * @param int ...$count
     * @return PasswordResetTokenRepository&Mock
     */
    private function mockTokenRepository(int ...$count): PasswordResetTokenRepository
    {
        $repository = m::mock(PasswordResetTokenRepository::class);
        $repository->shouldReceive('countByToken')->andReturn(...$count);
        return $repository;
    }

    /**
     * @param DateTimeImmutable $dateTime
     * @return DateTimeFactory&Mock
     */
    private function mockDateTimeFactory(DateTimeImmutable $dateTime): DateTimeFactory
    {
        $factory = m::mock(DateTimeFactory::class);
        $factory->shouldReceive('createModified')->andReturn($dateTime);
        return $factory;
    }

    /**
     * @return User&Mock
     */
    private function mockUser(): User
    {
        return m::mock(User::class);
    }

    /**
     * @return TokenGeneratorInterface&Mock
     */
    private function mockInfiniteTokenGenerator(): TokenGeneratorInterface
    {
        $tokenGenerator = m::mock(TokenGeneratorInterface::class);
        $tokenGenerator->shouldReceive('generateToken')->andReturn('');
        return $tokenGenerator;
    }

    /**
     * @return PasswordResetTokenRepository&Mock
     */
    private function mockTokenRepositoryNeverUnique(): PasswordResetTokenRepository
    {
        $repository = m::mock(PasswordResetTokenRepository::class);
        $repository->shouldReceive('countByToken')->andReturn(1);
        return $repository;
    }
}
