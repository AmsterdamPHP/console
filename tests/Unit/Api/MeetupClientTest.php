<?php

declare(strict_types=1);

namespace AmsterdamPHP\Console\Unit\Api;

use AmsterdamPHP\Console\Api\MeetupClient;
use AmsterdamPHP\Console\Unit\Util\GuzzleTestCase;
use GuzzleHttp\ClientInterface;
use Mockery;

class MeetupClientTest extends GuzzleTestCase
{
    private ClientInterface&Mockery\MockInterface $client;
    private MeetupClient $meetup;

    private const KEY     = 'some-key';
    private const BASEURL = 'https://some.path';
    private const GROUP   = 'group';

    protected function setUp(): void
    {
        $this->client = Mockery::mock(ClientInterface::class);
        $this->meetup = new MeetupClient(self::KEY, self::BASEURL);
        $this->validateStackPresence($this->meetup);
        $this->overrideClient($this->client, $this->meetup);
    }

    public function testGetUpcomingEventsForGroup(): void
    {
        $this->client->expects('get')
            ->withArgs(static fn ($url, $opts) => $opts['query']['key'] === [self::KEY]
                && $opts['query']['group_urlname'] === self::GROUP)
            ->andReturns($this->getFakeJsonAwareResponse(200, ['results' => []]))
            ->once();

        $this->meetup->getUpcomingEventsForGroup(self::GROUP);
    }
}
