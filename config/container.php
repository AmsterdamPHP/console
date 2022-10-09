<?php

use AmsterdamPHP\Console\Api\JoindInClient;
use AmsterdamPHP\Console\Api\MeetupClient;
use AmsterdamPHP\Console\Api\Middleware\JsonAwareResponse;
use AmsterdamPHP\Console\Api\SlackWebhookClient;
use DI\ContainerBuilder;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;

// Load configuration
$config = require __DIR__ . '/config.php';

// Build container
$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

// Define Deps


// Define services
$container->set(JoindInClient::class, new JoindInClient($config['joindin']['accessToken'], $config['joindin']['baseUrl']));
$container->set(MeetupClient::class, new MeetupClient($config['meetup']['apiKey'], $config['meetup']['baseUrl']));
$container->set(SlackWebhookClient::class, new SlackWebhookClient($config['slack']['webhookUrl']));
return $container;
