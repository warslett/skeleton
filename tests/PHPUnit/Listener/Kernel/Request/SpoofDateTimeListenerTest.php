<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Listener\Kernel\Request;

use App\Factory\DateTimeFactory;
use App\Listener\Kernel\Request\SpoofDateTimeListener;
use App\Tests\PHPUnit\TestCase;
use Mockery as m;
use Mockery\Mock;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class SpoofDateTimeListenerTest extends TestCase
{

    public function testOnKernelRequestRequestDoesNotHaveCookieDoesNothing()
    {
        $dateTimeFactory = $this->mockDateTimeFactory();
        $listener = new SpoofDateTimeListener($dateTimeFactory);

        $listener->onKernelRequest($this->mockEvent($this->mockRequest($this->mockParameterBagNoCookies())));

        $dateTimeFactory->shouldNotHaveReceived('spoofNow');
    }

    public function testOnKernelRequestHasCookieGetsCookie()
    {
        $listener = new SpoofDateTimeListener($this->mockDateTimeFactory());
        $parameterBag = $this->mockParameterBagWithCookie();

        $listener->onKernelRequest($this->mockEvent($this->mockRequest($parameterBag)));

        $parameterBag->shouldHaveReceived('get')->once()->with(SpoofDateTimeListener::SPOOF_DATE_TIME_COOKIE);
    }

    public function testOnKernelRequestHasCookieSpoofsNow()
    {
        $dateTimeFactory = $this->mockDateTimeFactory();
        $listener = new SpoofDateTimeListener($dateTimeFactory);
        $spoofedDateTimeString = '2020-11-04 16:00:00';
        $event = $this->mockEvent($this->mockRequest($this->mockParameterBagWithCookie($spoofedDateTimeString)));

        $listener->onKernelRequest($event);

        $dateTimeFactory->shouldHaveReceived('spoofNow')->once()->with($spoofedDateTimeString);
    }

    /**
     * @return DateTimeFactory&Mock
     */
    private function mockDateTimeFactory(): DateTimeFactory
    {
        $factory = m::mock(DateTimeFactory::class);
        $factory->shouldReceive('spoofNow');
        return $factory;
    }

    /**
     * @return ParameterBag&Mock
     */
    private function mockParameterBagNoCookies(): ParameterBag
    {
        $parameterBag = m::mock(ParameterBag::class);
        $parameterBag->shouldReceive('has')->andReturnFalse();
        return $parameterBag;
    }

    /**
     * @param string $spoofedDateTimeCookieString
     * @return ParameterBag&Mock
     */
    private function mockParameterBagWithCookie(string $spoofedDateTimeCookieString = ''): ParameterBag
    {
        $parameterBag = m::mock(ParameterBag::class);
        $parameterBag->shouldReceive('has')->andReturnTrue();
        $parameterBag->shouldReceive('get')->andReturn($spoofedDateTimeCookieString);
        return $parameterBag;
    }

    /**
     * @psalm-suppress NoInterfaceProperties - Symfony interface has public property
     * @param ParameterBag $cookies
     * @return Request&Mock
     */
    public function mockRequest(ParameterBag $cookies): Request
    {
        $request = m::mock(Request::class);
        $request->cookies = $cookies;
        return $request;
    }

    /**
     * @param Request $request
     * @return RequestEvent
     */
    private function mockEvent(Request $request): RequestEvent
    {
        $event = m::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')->andReturn($request);
        return $event;
    }
}
