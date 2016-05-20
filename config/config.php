<?php

// Load ENV vars
if (file_exists(__DIR__ . '/../.env')) {
    $container['env'] = new \Dotenv\Dotenv(__DIR__.'/../', '.env');
    $container['env']->load();
}

return [
    'slack_url' => getenv('SLACK_URL'),
    'meetup_api_key' => getenv('MEETUP_API_KEY'),
    'joindin' => [
        'base_url' => getenv('JOINDIN_URL'),
        'access_token' => getenv('JOINDIN_TOKEN')
    ],
];
