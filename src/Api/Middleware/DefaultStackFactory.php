<?php

declare(strict_types=1);

namespace AmsterdamPHP\Console\Api\Middleware;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;

final class DefaultStackFactory
{
    public static function createJsonHandlingStack(): HandlerStack
    {
        $stack = HandlerStack::create();
        $stack->push(Middleware::mapResponse(
            static function (ResponseInterface $response) {
                return new JsonAwareResponse(
                    $response->getStatusCode(),
                    $response->getHeaders(),
                    $response->getBody(),
                    $response->getProtocolVersion(),
                    $response->getReasonPhrase(),
                );
            },
        ), 'json_decode_middleware');

        return $stack;
    }
}
