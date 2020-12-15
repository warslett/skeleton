<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\Entity;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Tests\PHPUnit\TestCase;
use DateTimeImmutable;
use Mockery as m;
use Mockery\Mock;

class PasswordResetTokenTest extends TestCase
{

    public function testConstructHashesToken()
    {
        $tokenString = 't6y7u8i9o0p';

        $token = new PasswordResetToken($tokenString, new User(), new DateTimeImmutable());

        $this->assertSame(hash(PasswordResetToken::TOKEN_HASHING_ALGORITHM, $tokenString), $token->getHashedToken());
        $this->assertSame($tokenString, $token->getTokenPlainText());
    }
}
