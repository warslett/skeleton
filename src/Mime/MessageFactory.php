<?php

declare(strict_types=1);

namespace App\Mime;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class MessageFactory
{
    private string $mailFrom;

    public function __construct(string $mailFrom)
    {
        $this->mailFrom = $mailFrom;
    }

    /**
     * @return TemplatedEmail
     */
    public function createSystemEmail(): TemplatedEmail
    {
        $email = new TemplatedEmail();
        $email->from($this->mailFrom);
        return $email;
    }
}
