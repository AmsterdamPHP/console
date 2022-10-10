<?php

// Load ENV vars
if (file_exists(__DIR__ . '/../.env')) {
    $container['env'] = Dotenv\Dotenv::createImmutable(__DIR__.'/../', '.env');
    $container['env']->safeLoad();
}

return [
    'slack' => [
        'webhookUrl' => $_ENV['SLACK_URL'],
    ],
    'meetup' => [
        'baseUrl' => $_ENV['MEETUP_API_URL'],
        'apiKey' => $_ENV['MEETUP_API_KEY'],
    ],
    'joindin' => [
        'baseUrl' => $_ENV['JOINDIN_URL'],
        'accessToken' => $_ENV['JOINDIN_TOKEN'],
    ],
];
