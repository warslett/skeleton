<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use App\Listener\Kernel\Request\SpoofDateTimeListener;
use Behat\Behat\Context\Context;
use Behat\Mink\Session;

final class DateTimeContext implements Context
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @When I spoof the date for the session to :dateTimeString
     * @param string $dateTimeString
     * @return void
     */
    public function theDateAndTimeIs(string $dateTimeString): void
    {
        $this->session->setCookie(SpoofDateTimeListener::SPOOF_DATE_TIME_COOKIE, $dateTimeString);
    }
}
