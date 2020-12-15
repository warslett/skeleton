<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Mime;

use App\Mime\MessageFactory;
use App\Tests\PHPUnit\TestCase;

class MessageFactoryTest extends TestCase
{

    public function testCreateSystemEmailSetsFrom()
    {
        $from = 'foo@bar.co';
        $factory = new MessageFactory($from);

        $email = $factory->createSystemEmail();

        $this->assertSame(1, count($email->getFrom()));
        $this->assertArrayHasKey(0, $email->getFrom());
        $this->assertSame($from, $email->getFrom()[0]->getAddress());
    }
}
