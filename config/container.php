<?php

use AmsterdamPHP\Console\Api\JoindInClient;
use Crummy\Phlack\Phlack;
use DI\ContainerBuilder;
use DMS\Service\Meetup\AbstractMeetupClient;
use Joindin\Api\Client;

// Load configuration
$config = require __DIR__ . '/config.php';

// Build container
$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

// Define services
$container->set(Client::class, new Client($config['joindin']));
$container->set(JoindInClient::class, new JoindInClient($config['joindin']));
$container->set(AbstractMeetupClient::class, \DMS\Service\Meetup\MeetupKeyAuthClient::factory([
    'key' => $config['meetup_api_key']
]));
$container->set(Phlack::class, Phlack::factory($config['slack_url']));
return $container;
