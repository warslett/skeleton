<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\Registration\Factory;

use App\Domain\Entity\RegistrationToken;
use App\Domain\Repository\UserRepository;
use App\Domain\User\Registration\Exception\DuplicateEmailException;
use App\Domain\User\Registration\Exception\RegistrationTokenExpiredException;
use App\Domain\User\Registration\Factory\UserFactory;
use App\Factory\DateTimeFactory;
use App\Tests\PHPUnit\TestCase;
use DateTimeImmutable;
use Mockery as m;
use Mockery\Mock;

class UserFactoryTest extends TestCase
{

    /**
     * @return void
     * @throws DuplicateEmailException
     * @throws RegistrationTokenExpiredException
     */
    public function testCreateFromTokenStringTokenEmailNotUniqueThrowsException(): void
    {
        $factory = new UserFactory(
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-01 16:05')),
            $this->mockUserRepository(1)
        );

        $this->expectException(DuplicateEmailException::class);
        $this->expectExceptionMessage("Your email is already registered");

        $factory->createFromToken($this->mockToken(new DateTimeImmutable('2020-11-02 16:00')));
    }

    /**
     * @return void
     * @throws DuplicateEmailException
     * @throws RegistrationTokenExpiredException
     */
    public function testCreateFromTokenStringTokenExpiredThrowsException(): void
    {
        $factory = new UserFactory(
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-01 16:05')),
            $this->mockUserRepository(10)
        );

        $this->expectException(RegistrationTokenExpiredException::class);
        $this->expectExceptionMessage("Your registration has expired, please try again");

        $factory->createFromToken($this->mockToken(new DateTimeImmutable('2020-11-01 16:00')));
    }

    /**
     * @return void
     * @throws DuplicateEmailException
     * @throws RegistrationTokenExpiredException
     */
    public function testCreateFromTokenStringReturnsUserWithTokenEmail(): void
    {
        $email = 'john@acme.co';
        $factory = new UserFactory(
            $this->mockDateTimeFactory(new DateTimeImmutable('2020-11-01 16:05')),
            $this->mockUserRepository(0)
        );

        $user = $factory->createFromToken($this->mockToken(new DateTimeImmutable('2020-11-02 16:00'), $email));

        $this->assertSame($email, $user->getEmail());
    }

    /**
     * @param DateTimeImmutable $expiry
     * @param string $email
     * @return RegistrationToken&Mock
     */
    private function mockToken(DateTimeImmutable $expiry, string $email = ''): RegistrationToken
    {
        $token = m::mock(RegistrationToken::class);
        $token->shouldReceive('getExpiry')->andReturn($expiry);
        $token->shouldReceive('getEmail')->andReturn($email);
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
     * @param int $count
     * @return UserRepository&Mock
     */
    private function mockUserRepository(int $count): UserRepository
    {
        $repository = m::mock(UserRepository::class);
        $repository->shouldReceive('countByEmail')->andReturn($count);
        return $repository;
    }
}
