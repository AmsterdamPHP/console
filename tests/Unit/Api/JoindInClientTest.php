<?php

namespace AmsterdamPHP\Console\Unit\Api;

use AmsterdamPHP\Console\Api\JoindInClient;
use AmsterdamPHP\Console\Unit\Util\GuzzleTestCase;
use GuzzleHttp\ClientInterface;
use Mockery;
use function json_encode;
use const JSON_THROW_ON_ERROR;

class JoindInClientTest extends GuzzleTestCase
{

    private ClientInterface&Mockery\MockInterface $client;
    private JoindInClient $joindin;

    private const KEY = 'some-key';
    private const BASEURL = 'https://some.path';
    private const GROUP = 'group';

    protected function setUp(): void
    {
        $this->client  = Mockery::mock(ClientInterface::class);
        $this->joindin = new JoindInClient(self::KEY, self::BASEURL);
        $this->validateStackPresence($this->joindin);
        $this->overrideClient($this->client, $this->joindin);
    }

    public function testSubmitEvent(): void
    {
        $payload = [
            'name' => 'event_name',
        ];
        $this->client->expects('post')
            ->withArgs(fn($url, $opts) => $opts['body'] === json_encode($payload, JSON_THROW_ON_ERROR))
            ->andReturns($this->getFakeJsonAwareResponse(
                200,
                [],
                ['Location' => 'http://some.path/v2.1/events/34'])
            )
            ->once();

        $eventId = $this->joindin->submitEvent($payload);
        self::assertEquals(34, $eventId);
    }

    public function testSubmitEventReturnsEmptyEventIdOnBadLocation(): void
    {
        $payload = [
            'name' => 'event_name',
        ];
        $this->client->expects('post')
                     ->withArgs(fn($url, $opts) => $opts['body'] === json_encode($payload, JSON_THROW_ON_ERROR))
                     ->andReturns($this->getFakeJsonAwareResponse(
                         200,
                         [],
                         ['Location' => 'http://some.path/v2.2/blobs/34'])
                     )
                     ->once();

        $eventId = $this->joindin->submitEvent($payload);
        self::assertEquals('', $eventId);
    }

    public function testAddEventHost(): void
    {
        $host = 'new_host';
        $eventId = 34;
        $this->client->expects('post')
            ->withArgs(fn($url, $opts) => str_contains($url, $eventId) && $opts['body'] === '{"host_name":"'.$host.'"}'
            )
        ->andReturns($this->getFakeJsonAwareResponse(200))
        ->once();

        $this->joindin->addEventHost($eventId, $host);
    }
}
