<?php

declare(strict_types=1);

namespace App\Responder;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class JsonResponder
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param mixed $data
     * @param int $status
     * @return JsonResponse
     */
    public function buildJsonResponse($data, int $status = 200): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($data, 'json'), $status, [], true);
    }
}
