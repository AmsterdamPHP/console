<?php

namespace AmsterdamPHP\Console\Api;

use AmsterdamPHP\Console\Api\Middleware\DefaultStackFactory;
use AmsterdamPHP\Console\Api\Middleware\JsonAwareResponse;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Ramsey\Collection\Collection;
use Ramsey\Collection\CollectionInterface;

class MeetupClient
{
    private ClientInterface $client;
    private string $meetupKey;

    public function __construct(string $meetupKey, string $baseUrl)
    {
        $this->meetupKey = $meetupKey;
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'handler' => DefaultStackFactory::createJsonHandlingStack(),
       ]);
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function getUpcomingEventsForGroup(string $group): CollectionInterface
    {
        /** @var JsonAwareResponse $result */
        $result = $this->client->get('/2/events', [
            'query' => [
                'key'           => [$this->meetupKey],
                'group_urlname' => $group,
                'status'        => 'upcoming',
                'text_format'   => 'plain',
                'time'          => '0m,1m',
            ],
        ]);

        return new Collection('array', $result->getJson()['results']);
    }

}
