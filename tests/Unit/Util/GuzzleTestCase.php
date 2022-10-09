<?php

namespace AmsterdamPHP\Console\Unit\Util;

use AmsterdamPHP\Console\Api\Middleware\DefaultStackFactory;
use AmsterdamPHP\Console\Api\Middleware\JsonAwareResponse;
use GuzzleHttp\ClientInterface;
use JsonException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use ReflectionClass;
use ReflectionException;
use function json_encode;

class GuzzleTestCase extends MockeryTestCase
{
    public function overrideClient(ClientInterface $client, $instance): void
    {
        try {
            $relectedInstance = new ReflectionClass($instance);
            $clientProp = $relectedInstance->getProperty('client');
            $clientProp->setAccessible(true);
            $clientProp->setValue($instance, $client);
        } catch (ReflectionException $e) {
            $this->fail('Could not Reflect the Client: ' . $e->getMessage());
        }
    }

    public function getInnerClient(object $instance): ClientInterface
    {
        try {
            $relectedInstance = new ReflectionClass($instance);
            $clientProp = $relectedInstance->getProperty('client');
            $clientProp->setAccessible(true);
            return $clientProp->getValue($instance);
        } catch (ReflectionException $e) {
            $this->fail('Could not Reflect the Client: ' . $e->getMessage());
        }
    }

    public function getFakeJsonAwareResponse(int $statusCode, array $nonEncodedBody = [], array $headers = []): JsonAwareResponse
    {
        try {
            return new JsonAwareResponse(
                200,
                $headers + ['Content-Type' => 'application/json'],
                json_encode($nonEncodedBody, JSON_THROW_ON_ERROR)
            );
        } catch (JsonException $e) {
            $this->fail('Cound not create Fake Response: ' . $e->getMessage());
        }
    }

    public function validateStackPresence(object $instance): void
    {
        try {
            $client           = $this->getInnerClient($instance);
            $reflectedClient  = new ReflectionClass($client);
            $configProp       = $reflectedClient->getProperty('config');
            $configProp->setAccessible(true);
            $config = $configProp->getValue($client);

            $this->assertEquals($config['handler'], DefaultStackFactory::createJsonHandlingStack());
        } catch (ReflectionException $e) {
            $this->fail('Could not Reflect the Client: ' . $e->getMessage());
        }
    }
}
