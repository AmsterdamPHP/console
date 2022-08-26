<?php

namespace AmsterdamPHP\Console\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Collection;
use function json_encode;
use function var_dump;

final class JoindInClient extends Client
{
    const DEFAULT_BASE_URL = 'https://api.joind.in/v2.1';

    /**
     * Constructor
     */
    public function __construct(array $config) {
        $defaults = array('base_url' => self::DEFAULT_BASE_URL);
        $required = array('base_url');

        $configuration = Collection::fromConfig($config, $defaults, $required);

        parent::__construct($configuration->toArray());

        if ($configuration->get('access_token')) {
            $this->setDefaultOption('headers/Authorization', 'OAuth ' . $configuration->get('access_token'));
        }
        $this->setDefaultOption('headers/Accept-Charset', 'utf-8');
        $this->setDefaultOption('headers/Accept', 'application/json');
        $this->setDefaultOption('headers/Content-Type', 'application/json');
    }

    public function addEventHost($eventId, $eventHost)
    {
        $result = $this->post('v2.1/events/'.$eventId.'/hosts', ['body' => json_encode(['host_name' => $eventHost])]);
        return $result;
    }
}
