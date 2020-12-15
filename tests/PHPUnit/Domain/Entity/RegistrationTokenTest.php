<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\Entity;

use App\Domain\Entity\RegistrationToken;
use App\Tests\PHPUnit\TestCase;
use DateTimeImmutable;
use Mockery as m;
use Mockery\Mock;

class RegistrationTokenTest extends TestCase
{

    public function testConstructHashesToken()
    {
        $tokenString = 't6y7u8i9o0p';

        $token = new RegistrationToken($tokenString, 'john@acme.co', new DateTimeImmutable());

        $this->assertSame(hash(RegistrationToken::TOKEN_HASHING_ALGORITHM, $tokenString), $token->getHashedToken());
        $this->assertSame($tokenString, $token->getTokenPlainText());
    }
}
