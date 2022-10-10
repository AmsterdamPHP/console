<?php

declare(strict_types=1);

namespace AmsterdamPHP\Console\Unit\Api;

use AmsterdamPHP\Console\Api\SlackWebhookClient;
use AmsterdamPHP\Console\Unit\Util\GuzzleTestCase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Mockery;

use function json_encode;

use const JSON_THROW_ON_ERROR;

class SlackWebhookClientTest extends GuzzleTestCase
{
    private ClientInterface&Mockery\MockInterface $client;
    private SlackWebhookClient $slack;

    private const BASEURL = 'https://some.path';

    protected function setUp(): void
    {
        $this->client = Mockery::mock(ClientInterface::class);
        $this->slack  = new SlackWebhookClient(self::BASEURL);
        $this->overrideClient($this->client, $this->slack);
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function testSendMessage(): void
    {
        $message = ['text' => 'some text'];
        $this->client->expects('post')
            ->withArgs([self::BASEURL, ['body' => json_encode($message, JSON_THROW_ON_ERROR)]])
            ->andReturns($this->getFakeJsonAwareResponse(200))
            ->once();

        $this->slack->sendMessage($message);
    }
}
