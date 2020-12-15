<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\Entity;

use App\Domain\Entity\User;
use App\Tests\PHPUnit\TestCase;
use Mockery as m;
use Mockery\Mock;
use Symfony\Component\Uid\Ulid;

class UserTest extends TestCase
{

    public function testGetUserNameReturnsEmail()
    {
        $email = 'john@acme.co';
        $user = new User();
        $user->setEmail($email);

        $actual = $user->getUsername();

        $this->assertSame($email, $actual);
    }

    public function testGetUserNameNoEmailReturnsEmptyString()
    {
        $user = new User();

        $actual = $user->getUsername();

        $this->assertSame('', $actual);
    }

    public function testGetRoles()
    {
        $user = new User();

        $roles = $user->getRoles();

        $this->assertSame(['ROLE_USER'], $roles);
    }

    public function testEraseCredentials()
    {
        $user = new User();
        $user->setPlainPassword('foo');

        $user->eraseCredentials();

        $this->assertNull($user->getPlainPassword());
    }

    public function testSerialize(): void
    {
        $ulidString = '01BX5ZZKBKACTAV9WEVGEMMVRY';
        $email = 'john@acme.co';
        $password = 't6y7u8i9o0p';
        $user = new User();
        /** @var Ulid $ulid */
        $ulid = Ulid::fromString($ulidString);
        $user->setUlid($ulid);
        $user->setEmail($email);
        $user->setPassword($password);

        $actual = $user->serialize();

        $this->assertSame(serialize([
            $ulid,
            $email,
            $password
        ]), $actual);
    }

    public function testUnSerialize(): void
    {
        $ulidString = '01BX5ZZKBKACTAV9WEVGEMMVRY';
        $email = 'john@acme.co';
        $password = 't6y7u8i9o0p';
        /** @var Ulid $ulid */
        $ulid = Ulid::fromString($ulidString);
        $user = new User();

        $user->unserialize(serialize([
            $ulid,
            $email,
            $password
        ]));

        $this->assertEquals($ulid, $user->getUlid());
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($password, $user->getPassword());
    }
}
