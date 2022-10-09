<?php

namespace AmsterdamPHP\Console\Api\Middleware;

use GuzzleHttp\Psr7\Response;
use JsonException;
use Psr\Http\Message\StreamInterface;
use function array_shift;
use function json_decode;
use function var_dump;
use const JSON_THROW_ON_ERROR;

final class JsonAwareResponse extends Response
{
    private ?array $cachedJson = null;

    /**
     * @throws JsonException
     */
    public function getJson(): array|StreamInterface
    {
        if ($this->cachedJson) {
            return $this->cachedJson;
        }

        $body = $this->getBody();

        if (!str_contains($this->getHeaderLine('Content-Type'), 'application/json')) {
            return $body;
        }

        if ($body->getSize() === 0) {
            return [];
        }

        $this->cachedJson = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        return $this->cachedJson;
    }

    public function getLocationHeader(): string
    {
        $headerValue = $this->getHeader('Location');
        return array_shift($headerValue) ?? "";
    }
}
