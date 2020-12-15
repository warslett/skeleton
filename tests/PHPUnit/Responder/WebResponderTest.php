<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Responder;

use App\Responder\WebResponder;
use App\Tests\PHPUnit\TestCase;
use Mockery\Mock;
use Mockery as m;
use Mockery\MockInterface;
use Symfony\Component\Routing\Router;
use Twig\Environment as TwigEnvironment;
use Twig\Error\Error as TwigError;

class WebResponderTest extends TestCase
{

    /**
     * @return void
     * @throws TwigError
     */
    public function testCreateTemplateResponseRendersTemplate(): void
    {
        $twig = $this->mockTwig();
        $responder = new WebResponder($twig, $this->mockRouter());
        $template = 'my/template.html.twig';
        $context = ['foo' => 'bar'];

        $responder->createTemplateResponse($template, $context);

        $twig->shouldHaveReceived('render')->once()->with($template, $context);
    }

    /**
     * @return void
     * @throws TwigError
     */
    public function testCreateTemplateResponseReturnsResponseWithRenderedContent(): void
    {
        $content = "Lorem Ipsum";
        $responder = new WebResponder($this->mockTwig($content), $this->mockRouter());

        $response = $responder->createTemplateResponse('foo.html.twig', []);

        $this->assertSame($content, $response->getContent());
    }

    /**
     * @return void
     */
    public function testCreateRedirectResponseGeneratesRoute(): void
    {
        $router = $this->mockRouter();
        $responder = new WebResponder($this->mockTwig(), $router);
        $routeName = 'my_route';
        $routeParameters = ['foo' => 'bar'];

        $responder->createRedirectResponse($routeName, $routeParameters);

        $router->shouldHaveReceived('generate')->once()->with($routeName, $routeParameters);
    }

    /**
     * @return void
     */
    public function testCreateRedirectResponseReturnsRedirectResponseWithGeneratedUrl(): void
    {
        $url = "/foo/bar";
        $responder = new WebResponder($this->mockTwig(), $this->mockRouter($url));

        $response = $responder->createRedirectResponse('foo.html.twig', []);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame($url, $response->headers->get('Location'));
    }

    /**
     * @param string $renderedTemplate
     * @return TwigEnvironment&Mock
     */
    public function mockTwig(string $renderedTemplate = ''): TwigEnvironment
    {
        $twig = m::mock(TwigEnvironment::class);
        $twig->shouldReceive('render')->andReturn($renderedTemplate);
        return $twig;
    }

    /**
     * @param string $url
     * @return Router&Mock
     */
    private function mockRouter(string $url = '/'): Router
    {
        $twig = m::mock(Router::class);
        $twig->shouldReceive('generate')->andReturn($url);
        return $twig;
    }
}
