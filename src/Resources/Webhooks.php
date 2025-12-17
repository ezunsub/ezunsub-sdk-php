<?php

declare(strict_types=1);

namespace EZUnsub\Resources;

use EZUnsub\EZUnsubClient;

class Webhooks
{
    private EZUnsubClient $client;

    public function __construct(EZUnsubClient $client)
    {
        $this->client = $client;
    }

    /**
     * List webhooks
     *
     * @param string|null $orgId Filter by organization ID (admin only)
     * @return array<int, array<string, mixed>>
     */
    public function list(?string $orgId = null): array
    {
        $query = $orgId !== null ? ['orgId' => $orgId] : null;
        return $this->client->request('GET', '/api/webhooks', query: $query);
    }

    /**
     * Get a webhook by ID
     *
     * @param string $webhookId Webhook ID
     * @return array<string, mixed>
     */
    public function get(string $webhookId): array
    {
        return $this->client->request('GET', "/api/webhooks/{$webhookId}");
    }

    /**
     * Create a webhook
     *
     * @param string $name Webhook name
     * @param string $url Webhook URL (must be HTTPS)
     * @param array<int, string> $events Events to subscribe to
     * @param string $piiMode PII mode (full, hashes, none)
     * @param string|null $orgId Organization ID (admin only)
     * @return array<string, mixed> Created webhook with secret
     */
    public function create(
        string $name,
        string $url,
        array $events,
        string $piiMode = 'hashes',
        ?string $orgId = null
    ): array {
        $data = [
            'name' => $name,
            'url' => $url,
            'events' => $events,
            'piiMode' => $piiMode,
        ];

        if ($orgId !== null) {
            $data['orgId'] = $orgId;
        }

        return $this->client->request('POST', '/api/webhooks', json: $data);
    }

    /**
     * Update a webhook
     *
     * @param string $webhookId Webhook ID
     * @param string|null $name New webhook name
     * @param string|null $url New webhook URL
     * @param array<int, string>|null $events New events list
     * @param string|null $piiMode New PII mode
     * @param bool|null $isActive Enable/disable webhook
     * @return array<string, mixed>
     */
    public function update(
        string $webhookId,
        ?string $name = null,
        ?string $url = null,
        ?array $events = null,
        ?string $piiMode = null,
        ?bool $isActive = null
    ): array {
        $data = [];

        if ($name !== null) {
            $data['name'] = $name;
        }
        if ($url !== null) {
            $data['url'] = $url;
        }
        if ($events !== null) {
            $data['events'] = $events;
        }
        if ($piiMode !== null) {
            $data['piiMode'] = $piiMode;
        }
        if ($isActive !== null) {
            $data['isActive'] = $isActive;
        }

        return $this->client->request('PATCH', "/api/webhooks/{$webhookId}", json: $data);
    }

    /**
     * Delete a webhook
     *
     * @param string $webhookId Webhook ID
     * @return array<string, mixed>
     */
    public function delete(string $webhookId): array
    {
        return $this->client->request('DELETE', "/api/webhooks/{$webhookId}");
    }

    /**
     * Rotate webhook secret
     *
     * @param string $webhookId Webhook ID
     * @return array<string, mixed> Webhook with new secret
     */
    public function rotateSecret(string $webhookId): array
    {
        return $this->client->request('POST', "/api/webhooks/{$webhookId}/rotate-secret");
    }

    /**
     * Send a test webhook
     *
     * @param string $webhookId Webhook ID
     * @return array<string, mixed> Test result
     */
    public function test(string $webhookId): array
    {
        return $this->client->request('POST', "/api/webhooks/{$webhookId}/test");
    }

    /**
     * Get webhook delivery history
     *
     * @param string $webhookId Webhook ID
     * @param int $limit Max results (default: 50, max: 100)
     * @param int $offset Offset for pagination
     * @return array<string, mixed>
     */
    public function deliveries(string $webhookId, int $limit = 50, int $offset = 0): array
    {
        return $this->client->request(
            'GET',
            "/api/webhooks/{$webhookId}/deliveries",
            query: ['limit' => $limit, 'offset' => $offset]
        );
    }

    /**
     * Get available webhook events
     *
     * @return array<string, mixed>
     */
    public function events(): array
    {
        return $this->client->request('GET', '/api/webhooks/events/list');
    }
}
