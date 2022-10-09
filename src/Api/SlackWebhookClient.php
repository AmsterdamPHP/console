<?php

namespace AmsterdamPHP\Console\Api;

use AmsterdamPHP\Console\Api\Middleware\JsonAwareResponse;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use function json_encode;

class SlackWebhookClient
{
    private ClientInterface $client;

    public function __construct(private readonly string $webhookUrl)
    {
        $this->client = new Client();
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function sendMessage(array $message): ResponseInterface
    {
        /** @var JsonAwareResponse $result */
        $result = $this->client->post($this->webhookUrl, [
            'body' => json_encode($message, JSON_THROW_ON_ERROR),
        ]);

        return $result;
    }
}
