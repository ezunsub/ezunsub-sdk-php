<?php

declare(strict_types=1);

namespace EZUnsub;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use EZUnsub\Exceptions\EZUnsubException;
use EZUnsub\Exceptions\AuthenticationException;
use EZUnsub\Exceptions\ValidationException;
use EZUnsub\Exceptions\NotFoundException;
use EZUnsub\Exceptions\RateLimitException;
use EZUnsub\Resources\Contacts;
use EZUnsub\Resources\Webhooks;
use EZUnsub\Resources\Links;
use EZUnsub\Resources\Offers;
use EZUnsub\Resources\Exports;

/**
 * EZUnsub API Client
 *
 * @example
 * ```php
 * $client = new EZUnsubClient(
 *     apiKey: 'your-api-key',
 *     baseUrl: 'https://your-ezunsub-instance.com'
 * );
 *
 * // List contacts
 * $contacts = $client->contacts()->list();
 *
 * // Create webhook
 * $webhook = $client->webhooks()->create(
 *     name: 'My Webhook',
 *     url: 'https://my-app.com/webhooks/ezunsub',
 *     events: ['contact.created', 'contact.updated']
 * );
 * ```
 */
class EZUnsubClient
{
    private Client $httpClient;
    private string $apiKey;
    private string $baseUrl;

    private ?Contacts $contacts = null;
    private ?Webhooks $webhooks = null;
    private ?Links $links = null;
    private ?Offers $offers = null;
    private ?Exports $exports = null;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.ezunsub.com',
        float $timeout = 30.0
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');

        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $timeout,
            'headers' => [
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'ezunsub-php/0.1.0',
            ],
        ]);
    }

    /**
     * Make an API request
     *
     * @param string $method HTTP method
     * @param string $path API path
     * @param array<string, mixed>|null $json Request body
     * @param array<string, mixed>|null $query Query parameters
     * @return array<string, mixed>
     * @throws EZUnsubException
     */
    public function request(
        string $method,
        string $path,
        ?array $json = null,
        ?array $query = null
    ): array {
        try {
            $options = [];

            if ($json !== null) {
                $options['json'] = $json;
            }

            if ($query !== null) {
                $options['query'] = $query;
            }

            $response = $this->httpClient->request($method, $path, $options);
            $body = (string) $response->getBody();

            if (empty($body)) {
                return [];
            }

            return json_decode($body, true) ?? [];
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        } catch (GuzzleException $e) {
            throw new EZUnsubException('Request failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle request exceptions and convert to appropriate EZUnsub exceptions
     *
     * @throws EZUnsubException
     */
    private function handleRequestException(RequestException $e): never
    {
        $response = $e->getResponse();

        if ($response === null) {
            throw new EZUnsubException('Request failed: ' . $e->getMessage());
        }

        $statusCode = $response->getStatusCode();
        $body = json_decode((string) $response->getBody(), true);
        $message = $body['error'] ?? 'Request failed';

        match ($statusCode) {
            401 => throw new AuthenticationException($message),
            403 => throw new EZUnsubException($message, 403),
            404 => throw new NotFoundException($message),
            429 => throw new RateLimitException(
                $message,
                $response->hasHeader('Retry-After')
                    ? (int) $response->getHeader('Retry-After')[0]
                    : null
            ),
            400 => throw new ValidationException($message),
            default => throw new EZUnsubException($message, $statusCode),
        };
    }

    /**
     * Get Contacts resource
     */
    public function contacts(): Contacts
    {
        if ($this->contacts === null) {
            $this->contacts = new Contacts($this);
        }
        return $this->contacts;
    }

    /**
     * Get Webhooks resource
     */
    public function webhooks(): Webhooks
    {
        if ($this->webhooks === null) {
            $this->webhooks = new Webhooks($this);
        }
        return $this->webhooks;
    }

    /**
     * Get Links resource
     */
    public function links(): Links
    {
        if ($this->links === null) {
            $this->links = new Links($this);
        }
        return $this->links;
    }

    /**
     * Get Offers resource
     */
    public function offers(): Offers
    {
        if ($this->offers === null) {
            $this->offers = new Offers($this);
        }
        return $this->offers;
    }

    /**
     * Get Exports resource
     */
    public function exports(): Exports
    {
        if ($this->exports === null) {
            $this->exports = new Exports($this);
        }
        return $this->exports;
    }
}
