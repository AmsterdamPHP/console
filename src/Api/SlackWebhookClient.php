<?php

declare(strict_types=1);

namespace AmsterdamPHP\Console\Api;

use AmsterdamPHP\Console\Api\Middleware\JsonAwareResponse;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

use function assert;
use function json_encode;

use const JSON_THROW_ON_ERROR;

class SlackWebhookClient
{
    private ClientInterface $client;

    public function __construct(private readonly string $webhookUrl)
    {
        $this->client = new Client();
    }

    /**
     * @param string[] $message
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function sendMessage(array $message): ResponseInterface
    {
        return $this->client->post($this->webhookUrl, [
            'body' => json_encode($message, JSON_THROW_ON_ERROR),
        ]);
    }
}
