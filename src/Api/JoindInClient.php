<?php

namespace AmsterdamPHP\Console\Api;

use AmsterdamPHP\Console\Api\Middleware\DefaultStackFactory;
use AmsterdamPHP\Console\Api\Middleware\JsonAwareResponse;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use function json_encode;
use function preg_match;
use const JSON_THROW_ON_ERROR;

class JoindInClient
{
    private ClientInterface $client;

    /**
     * Constructor
     */
    public function __construct(string $token, string $baseUrl) {
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => 'OAuth ' . $token,
                'Accept-Charset' => 'utf-8',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'handler' => DefaultStackFactory::createJsonHandlingStack(),
        ]);
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function addEventHost($eventId, $eventHost): JsonAwareResponse
    {
        /** @var JsonAwareResponse $result */
        $result = $this->client->post('v2.1/events/'.$eventId.'/hosts', [
            'body' => json_encode(['host_name' => $eventHost], JSON_THROW_ON_ERROR)
        ]);

        return $result;
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function submitEvent($event): string
    {
        /** @var JsonAwareResponse $result */
        $result = $this->client->post('v2.1/events', [
            'body' => json_encode($event, JSON_THROW_ON_ERROR)
        ]);

        return $this->extractIdFromLocation($result->getLocationHeader());
    }

    private function extractIdFromLocation($locationUrl): string
    {
        $matches = [];
        preg_match(
            "/events\/(\d*)/",
            $locationUrl,
            $matches
        );

        return $matches[1] ?? '';
    }
}
