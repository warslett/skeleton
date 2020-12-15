<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Responder;

use App\Responder\JsonResponder;
use App\Tests\PHPUnit\TestCase;
use Mockery as m;
use Mockery\Mock;
use Symfony\Component\Serializer\SerializerInterface;

class JsonResponderTest extends TestCase
{

    public function testBuildJsonResponseSerializesData(): void
    {
        $serializer = $this->mockSerializer();
        $responder = new JsonResponder($serializer);
        $data = ['foo' => 'bar'];

        $responder->buildJsonResponse($data);

        $serializer->shouldHaveReceived('serialize')->once()->with($data, 'json');
    }

    public function testBuildJsonContainsSerializedData(): void
    {
        $serializedData = "{'foo': 'bar'}";
        $responder = new JsonResponder($this->mockSerializer($serializedData));

        $response = $responder->buildJsonResponse(['foo' => 'bar']);

        $this->assertSame($serializedData, $response->getContent());
    }

    public function testBuildJsonContainsStatus(): void
    {
        $status = 201;
        $responder = new JsonResponder($this->mockSerializer());

        $response = $responder->buildJsonResponse(['foo' => 'bar'], $status);

        $this->assertSame($status, $response->getStatusCode());
    }

    public function testBuildJsonContainsJsonHeader(): void
    {
        $status = 201;
        $responder = new JsonResponder($this->mockSerializer());

        $response = $responder->buildJsonResponse(['foo' => 'bar'], $status);

        $this->assertSame('application/json', $response->headers->get('Content-Type'));
    }

    /**
     * @param string $serializedData
     * @return SerializerInterface&Mock
     */
    private function mockSerializer(string $serializedData = ''): SerializerInterface
    {
        $serializer = m::mock(SerializerInterface::class);
        $serializer->shouldReceive('serialize')->andReturn($serializedData);
        return $serializer;
    }
}
