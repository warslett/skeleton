<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\Registration\Factory;

use App\Domain\Entity\RegistrationToken;
use App\Domain\Repository\RegistrationTokenRepository;
use App\Domain\User\Registration\Factory\RegistrationTokenFactory;
use App\Factory\DateTimeFactory;
use App\Tests\PHPUnit\TestCase;
use DateTimeImmutable;
use Mockery\Mock;
use RuntimeException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Mockery as m;

class RegistrationTokenFactoryTest extends TestCase
{

    public function testCreateGeneratesToken(): void
    {
        $tokenGenerator = $this->mockTokenGenerator('foo');
        $registrationTokenFactory = new RegistrationTokenFactory(
            $tokenGenerator,
            $this->mockTokenRepository(0),
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-02T16:00:00.0000Z')),
        );

        $registrationTokenFactory->create('john@acme.co');

        $tokenGenerator->shouldHaveReceived('generateToken')->once();
    }

    public function testCreateCountsRegistrationTokensForTokenString(): void
    {
        $tokenString = 'foo';
        $tokenRepository = $this->mockTokenRepository(0);
        $registrationTokenFactory = new RegistrationTokenFactory(
            $this->mockTokenGenerator($tokenString),
            $tokenRepository,
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-02T16:00:00.0000Z')),
        );

        $registrationTokenFactory->create('john@acme.co');

        $tokenRepository->shouldHaveReceived('countByToken')->once()->with($tokenString);
    }

    public function testCreateCreatesExpiryDate(): void
    {
        $dateTimeFactory = $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-02T16:00:00.0000Z'));
        $registrationTokenFactory = new RegistrationTokenFactory(
            $this->mockTokenGenerator('foo'),
            $this->mockTokenRepository(0),
            $dateTimeFactory,
        );

        $registrationTokenFactory->create('john@acme.co');

        $dateTimeFactory->shouldHaveReceived('createModified')->once()->with('+1 day');
    }

    public function testCreateReturnsRegistrationToken(): void
    {
        $token = 'foo';
        $email = 'john@acme.co';
        $expiry = new DateTimeImmutable('2020-11-02T16:00:00.0000Z');
        $registrationTokenFactory = new RegistrationTokenFactory(
            $this->mockTokenGenerator($token),
            $this->mockTokenRepository(0),
            $this->mockDateTimeFactory($expiry)
        );

        $registrationToken = $registrationTokenFactory->create($email);

        $this->assertSame($email, $registrationToken->getEmail());
        $this->assertSame(
            hash(RegistrationToken::TOKEN_HASHING_ALGORITHM, $token),
            $registrationToken->getHashedToken()
        );
        $this->assertSame($expiry, $registrationToken->getExpiry());
    }

    public function testCreateGeneratedTokenAlreadyInUseGeneratesAnotherToken(): void
    {
        $tokenGenerator = $this->mockTokenGenerator('foo', 'bar');
        $tokenRepository = $this->mockTokenRepository(1, 0);
        $registrationTokenFactory = new RegistrationTokenFactory(
            $tokenGenerator,
            $tokenRepository,
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-02T16:00:00.0000Z'))
        );

        $registrationTokenFactory->create('john@acme.co');

        $tokenGenerator->shouldHaveReceived('generateToken')->twice();
        $tokenRepository->shouldHaveReceived('countByToken')->twice();
    }

    public function testCreateGeneratedTokenAlreadyInUseReturnsSecondToken(): void
    {
        $token = 'bar';
        $email = 'john@acme.co';
        $expiry = new DateTimeImmutable('2020-11-02T16:00:00.0000Z');
        $registrationTokenFactory = new RegistrationTokenFactory(
            $this->mockTokenGenerator('foo', $token),
            $this->mockTokenRepository(1, 0),
            $this->mockDateTimeFactory($expiry)
        );

        $registrationToken = $registrationTokenFactory->create($email);

        $this->assertSame(
            hash(RegistrationToken::TOKEN_HASHING_ALGORITHM, $token),
            $registrationToken->getHashedToken()
        );
        $this->assertSame($email, $registrationToken->getEmail());
        $this->assertSame($expiry, $registrationToken->getExpiry());
    }

    public function testCreateFailsToGenerateUniqueRegistrationTokenThrowsException(): void
    {
        $expiry = new DateTimeImmutable('2020-11-02T16:00:00.0000Z');
        $registrationTokenFactory = new RegistrationTokenFactory(
            $this->mockInfiniteTokenGenerator(),
            $this->mockTokenRepositoryNeverUnique(),
            $this->mockDateTimeFactory($expiry)
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed to generate unique registration token");

        $registrationTokenFactory->create('john@acme.co');
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
     * @return RegistrationTokenRepository&Mock
     */
    private function mockTokenRepository(int ...$count): RegistrationTokenRepository
    {
        $repository = m::mock(RegistrationTokenRepository::class);
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
     * @return TokenGeneratorInterface&Mock
     */
    private function mockInfiniteTokenGenerator(): TokenGeneratorInterface
    {
        $tokenGenerator = m::mock(TokenGeneratorInterface::class);
        $tokenGenerator->shouldReceive('generateToken')->andReturn('');
        return $tokenGenerator;
    }

    /**
     * @return RegistrationTokenRepository&Mock
     */
    private function mockTokenRepositoryNeverUnique(): RegistrationTokenRepository
    {
        $repository = m::mock(RegistrationTokenRepository::class);
        $repository->shouldReceive('countByToken')->andReturn(1);
        return $repository;
    }
}
