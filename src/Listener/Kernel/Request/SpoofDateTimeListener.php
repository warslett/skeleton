<?php

declare(strict_types=1);

namespace App\Listener\Kernel\Request;

use App\Factory\DateTimeFactory;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class SpoofDateTimeListener
{
    public const SPOOF_DATE_TIME_COOKIE = '_spoof_date_time';
    private DateTimeFactory $dateTimeFactory;

    public function __construct(DateTimeFactory $dateTimeFactory)
    {
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @param RequestEvent $event
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->cookies->has(self::SPOOF_DATE_TIME_COOKIE)) {
            /** @var string $spoofedDateTimeString */
            $spoofedDateTimeString = $request->cookies->get(self::SPOOF_DATE_TIME_COOKIE);
            $this->dateTimeFactory->spoofNow($spoofedDateTimeString);
        }
    }
}
