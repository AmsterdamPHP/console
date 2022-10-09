<?php

namespace AmsterdamPHP\Console\Unit\Api\Middleware;

use AmsterdamPHP\Console\Api\Middleware\JsonAwareResponse;
use JsonException;
use PHPUnit\Framework\TestCase;
use function json_encode;
use const JSON_THROW_ON_ERROR;

class JsonAwareResponseTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testGetJsonCanHandleJson(): void
    {
        $body = ['some' => 'data'];
        $response = new JsonAwareResponse(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($body, JSON_THROW_ON_ERROR)
        );

        self::assertEquals($body, $response->getJson());
    }

    public function testGetJsonCanHandleEmptyResponse(): void
    {
        $response = new JsonAwareResponse(
            200,
            ['Content-Type' => 'application/json'],
            ''
        );

        self::assertEquals([], $response->getJson());
    }

    public function testGetJsonCanHandleNonJson(): void
    {
        $body = "Some nonjson content";
        $response = new JsonAwareResponse(
            200,
            body: $body
        );

        self::assertEquals($body, $response->getJson());
    }

    /**
     * @throws JsonException
     */
    public function testGetJsonIsCached(): void
    {
        $body = ['some' => 'data'];
        $response = new JsonAwareResponse(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($body, JSON_THROW_ON_ERROR)
        );

        self::assertEquals($body, $response->getJson());
        self::assertEquals($body, $response->getJson());
    }

    public function testGetLocationHeaderWorks(): void
    {
        $location      = 'https://path/to/event';
        $response = new JsonAwareResponse(200, ['Location' => $location]);
        self::assertEquals($location, $response->getLocationHeader());
    }
    public function testGetLocationHeaderHandlesMissingHeader(): void
    {
        $response = new JsonAwareResponse(200);
        self::assertEquals("", $response->getLocationHeader());
    }
}
